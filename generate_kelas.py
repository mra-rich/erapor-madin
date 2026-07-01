import re
import os

files = ['cetak_sampul.php', 'cetak_biodata.php', 'preview_rapot.php', 'cetak_semua.php']

for file in files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()

    # We want to replace the single student logic with a loop that wraps the HTML body.
    # First, let's find the `?>` right before `<!DOCTYPE html>`
    parts = content.split('<!DOCTYPE html>', 1)
    if len(parts) != 2:
        continue
    
    php_header = parts[0]
    html_part = '<!DOCTYPE html>' + parts[1]
    
    # We will modify php_header to just require koneksi and check access, then fetch the list of ids.
    # Then we will wrap the data fetching and HTML inside a foreach loop.
    
    new_file = file.replace('.php', '_kelas.php')
    
    # The new structure:
    new_content = """<?php
require_once 'koneksi.php';
require_once 'cek_sesi.php';
restrict_roles(RBAC_VIEW_REPORTS);

if (!isset($_GET['kelas'])) {
    die("Kelas tidak ditemukan.");
}

$id_kelas = (int)$_GET['kelas'];
$semester = isset($_GET['smt']) ? (int)$_GET['smt'] : 1;

$is_wali = ($_SESSION['peran'] === 'Wali Kelas');
$id_pengguna = (int)$_SESSION['id_pengguna'];

// Get all siswa in this class
$sql_list = "SELECT id_siswa FROM siswa WHERE id_kelas = ?";
if ($is_wali) {
    $sql_list = "SELECT s.id_siswa FROM siswa s JOIN kelas k ON s.id_kelas = k.id_kelas WHERE s.id_kelas = ? AND k.id_wali_kelas = ?";
}
$stmt_list = mysqli_prepare($koneksi, $sql_list);
if ($is_wali) {
    mysqli_stmt_bind_param($stmt_list, "ii", $id_kelas, $id_pengguna);
} else {
    mysqli_stmt_bind_param($stmt_list, "i", $id_kelas);
}
mysqli_stmt_execute($stmt_list);
$res_list = mysqli_stmt_get_result($stmt_list);
$siswas = [];
while ($row = mysqli_fetch_assoc($res_list)) {
    $siswas[] = (int)$row['id_siswa'];
}
if (empty($siswas)) {
    die("Tidak ada siswa di kelas ini.");
}

// Extract helper functions from original file to avoid redeclaration in loop
"""
    
    # Extract functions from php_header
    functions_str = ""
    def extract_func(m):
        global functions_str
        functions_str += m.group(0) + "\n"
        return ""
    
    php_header = re.sub(r'function\s+[a-zA-Z0-9_]+\s*\([^)]*\)\s*\{.*?\n\}', extract_func, php_header, flags=re.DOTALL)
    
    new_content += functions_str + "?>\n"
    
    # HTML wrapper
    # We will extract everything inside <body> and loop it.
    body_match = re.search(r'(<body[^>]*>)(.*?)(</body>)', html_part, flags=re.DOTALL | re.IGNORECASE)
    if not body_match:
        continue
    
    head_part = html_part[:body_match.start(2)] # up to <body>
    tail_part = html_part[body_match.end(2):] # from </body>
    
    inner_body = body_match.group(2)
    
    # Strip the original require 'koneksi.php' etc from php_header, keep only the data fetching logic
    # Find where $id_siswa is defined and take everything after that
    
    fetch_logic = ""
    match_id = re.search(r'\$id_siswa\s*=\s*\(int\)\$_GET\[\'id\'\];(.*)', php_header, flags=re.DOTALL)
    if match_id:
        fetch_logic = match_id.group(1)
        # Remove the IDOR check since we already verified class access
        # Just replace the query with a simple fetch
        fetch_logic = re.sub(r'\$is_wali\s*=\s*\(\$_SESSION.*?;', '', fetch_logic, flags=re.DOTALL)
        fetch_logic = re.sub(r'\$sql\s*=\s*"SELECT s\.\*, k\.nama_kelas FROM siswa s LEFT JOIN kelas k ON s\.id_kelas = k\.id_kelas WHERE s\.id_siswa = \?";.*?mysqli_stmt_close\(\$stmt\);', 
"""$query_siswa = mysqli_query($koneksi, "SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.id_kelas = k.id_kelas WHERE s.id_siswa = $id_siswa");
$siswa = mysqli_fetch_assoc($query_siswa);""", fetch_logic, flags=re.DOTALL)
        
        # for cetak_semua.php and preview_rapot.php, it has different query logic for siswa
        fetch_logic = re.sub(r'\$query_siswa\s*=\s*"SELECT s\.\*, k\.nama_kelas, t\.tahun_ajaran, t\.id_transaksi.*?\$id_transaksi = \$siswa\[\'id_transaksi\'\];', 
"""$query_siswa = mysqli_query($koneksi, "SELECT s.*, k.nama_kelas, t.tahun_ajaran, t.id_transaksi 
                FROM siswa s 
                LEFT JOIN kelas k ON s.id_kelas = k.id_kelas 
                LEFT JOIN transaksi_raport t ON s.id_siswa = t.id_siswa AND t.semester = $semester
                WHERE s.id_siswa = $id_siswa");
$siswa = mysqli_fetch_assoc($query_siswa);
$id_transaksi = $siswa['id_transaksi'] ?? null;""", fetch_logic, flags=re.DOTALL)

    new_content += head_part
    new_content += "\n<?php foreach ($siswas as $id_siswa) { ?>\n"
    new_content += "<?php\n" + fetch_logic + "\n?>\n"
    new_content += "<div style='page-break-after: always;'>"
    new_content += inner_body
    new_content += "</div>\n"
    new_content += "<?php } ?>\n"
    new_content += tail_part
    
    with open(new_file, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print(f"Generated {new_file}")


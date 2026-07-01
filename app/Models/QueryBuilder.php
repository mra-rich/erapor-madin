<?php
/**
 * Kelas QueryBuilder (Lightweight ORM)
 * Bertugas mengotomatisasi penyusunan kueri INSERT dan UPDATE 
 * tanpa perlu mendefinisikan tipe data 's', 'i', dsb secara manual.
 */
class QueryBuilder {
    private $koneksi;

    public function __construct($koneksi) {
        $this->koneksi = $koneksi;
    }

    /**
     * Memasukkan data ke dalam database
     * @param string $table Nama tabel
     * @param array $data Array asosiatif ['nama_kolom' => 'nilai']
     * @return int|bool Mengembalikan insert_id jika sukses, false jika gagal
     */
    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = mysqli_prepare($this->koneksi, $query);
        
        if ($stmt) {
            $types = $this->getTypes($data);
            $values = array_values($data);
            
            // Dynamic Bind Param untuk PHP 8 (menggunakan splat operator)
            mysqli_stmt_bind_param($stmt, $types, ...$values);
            
            if (mysqli_stmt_execute($stmt)) {
                $insert_id = mysqli_insert_id($this->koneksi);
                mysqli_stmt_close($stmt);
                return $insert_id ?: true;
            }
            mysqli_stmt_close($stmt);
        }
        return false;
    }

    /**
     * Mengubah data di dalam database
     * @param string $table Nama tabel
     * @param array $data Array asosiatif untuk kolom yang diubah ['kolom' => 'nilai']
     * @param array $where Kondisi (WHERE) ['kolom' => 'nilai']
     * @return bool True jika berhasil, false jika gagal
     */
    public function update($table, $data, $where) {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "$key = ?";
        }
        $setClause = implode(", ", $setParts);
        
        $whereParts = [];
        foreach ($where as $key => $value) {
            $whereParts[] = "$key = ?";
        }
        $whereClause = implode(" AND ", $whereParts);
        
        $query = "UPDATE $table SET $setClause WHERE $whereClause";
        $stmt = mysqli_prepare($this->koneksi, $query);
        
        if ($stmt) {
            $types = $this->getTypes($data) . $this->getTypes($where);
            $values = array_merge(array_values($data), array_values($where));
            
            mysqli_stmt_bind_param($stmt, $types, ...$values);
            
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }
    
    /**
     * Metode internal untuk membaca tipe data dari value
     */
    private function getTypes($data) {
        $types = "";
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= "i";
            } elseif (is_float($value)) {
                $types .= "d";
            } else {
                $types .= "s"; // default string
            }
        }
        return $types;
    }
}
?>

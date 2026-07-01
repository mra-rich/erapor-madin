<?php
// Mock request
$_SERVER['REQUEST_METHOD'] = 'POST';

// Override file_get_contents to read from our test payload
class MockStreamWrapper {
    private $position;
    private $data;

    public function stream_open($path, $mode, $options, &$opened_path) {
        $this->data = file_get_contents('test_payload.json');
        $this->position = 0;
        return true;
    }

    public function stream_read($count) {
        $ret = substr($this->data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_eof() {
        return $this->position >= strlen($this->data);
    }
}
stream_wrapper_unregister("php");
stream_wrapper_register("php", "MockStreamWrapper");

// Include the script, but we will mock session BEFORE `koneksi.php` by catching it
session_start();
$_SESSION['id_pengguna'] = 1;
$_SESSION['peran'] = 'Admin';

require 'proses_import_guru.php';

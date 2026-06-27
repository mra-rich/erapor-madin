<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script>
    function cetakWord() {
        const container = document.querySelector('.container').innerHTML;
        const content = `
            <html xmlns:o='urn:schemas-microsoft-com:office:office'
                  xmlns:w='urn:schemas-microsoft-com:office:word'
                  xmlns='http://www.w3.org/TR/REC-html40'>
            <head>
                <meta charset='utf-8'>
                <title>Rapot Santri</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .container { border: 2px solid black; padding: 20px; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid black; padding: 8px; text-align: center; }
                    th { background-color: #f2f2f2; }
                </style>
            </head>
            <body>
                <div class="container">${container}</div>
            </body>
            </html>
        `;

        // Konversi ke Blob dan simpan sebagai .doc
        const blob = new Blob(['\ufeff', content], { type: 'application/msword' });
        saveAs(blob, 'cetak_rapot.doc');
    }
</script>
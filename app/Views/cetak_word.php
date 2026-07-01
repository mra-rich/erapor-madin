<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script src="https://unpkg.com/html-docx-js/dist/html-docx.js"></script>
<script>
    function cetakWord() {
        const container = document.querySelector('.container').innerHTML;
        const content = `<!DOCTYPE html>
            <html>
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

        // Konversi ke Blob .docx menggunakan html-docx-js
        const converted = htmlDocx.asBlob(content);
        saveAs(converted, 'cetak_rapot.docx');
    }
</script>
from zipfile import ZipFile, ZIP_DEFLATED
from datetime import datetime

output = 'laporan_uas_toko_sepatu.docx'
content = f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>
    <w:p>
      <w:pPr><w:jc w:val="center"/></w:pPr>
      <w:r><w:rPr><w:b/><w:sz w:val="28"/></w:rPr><w:t>LAPORAN UAS PROGRAM TOKO SEPATU</w:t></w:r>
    </w:p>
    <w:p><w:r><w:t>Nama Program: Sistem Informasi Manajemen Toko Sepatu</w:t></w:r></w:p>
    <w:p><w:r><w:t>Teknologi: PHP, MySQL, Bootstrap, Dompdf</w:t></w:r></w:p>
    <w:p><w:r><w:t>Penjelasan Singkat:</w:t></w:r></w:p>
    <w:p><w:r><w:t>Program ini dirancang untuk membantu admin mengelola data sepatu, mencatat pembelian, mengatur pengeluaran, dan mencatat stok keluar secara manual. Sistem ini juga menyediakan fitur login admin dan export laporan ke PDF.</w:t></w:r></w:p>
    <w:p><w:r><w:t>Fitur Utama:</w:t></w:r></w:p>
    <w:p><w:r><w:t>1. Login admin</w:t></w:r></w:p>
    <w:p><w:r><w:t>2. CRUD data sepatu</w:t></w:r></w:p>
    <w:p><w:r><w:t>3. Pencatatan pembelian</w:t></w:r></w:p>
    <w:p><w:r><w:t>4. Pencatatan pengeluaran</w:t></w:r></w:p>
    <w:p><w:r><w:t>5. Input stok keluar manual</w:t></w:r></w:p>
    <w:p><w:r><w:t>6. Laporan PDF yang menarik</w:t></w:r></w:p>
    <w:p><w:r><w:t>Struktur Database:</w:t></w:r></w:p>
    <w:p><w:r><w:t>- sepatu</w:t></w:r></w:p>
    <w:p><w:r><w:t>- users</w:t></w:r></w:p>
    <w:p><w:r><w:t>- pembelian</w:t></w:r></w:p>
    <w:p><w:r><w:t>- pengeluaran</w:t></w:r></w:p>
    <w:p><w:r><w:t>- stok_keluar</w:t></w:r></w:p>
    <w:p><w:r><w:t>Kesimpulan:</w:t></w:r></w:p>
    <w:p><w:r><w:t>Program ini berhasil dibuat sebagai aplikasi sederhana namun lengkap untuk kebutuhan manajemen toko sepatu. Aplikasi ini sudah mendukung proses pencatatan dan pelaporan yang cukup baik untuk kebutuhan tugas UAS.</w:t></w:r></w:p>
    <w:p><w:r><w:t>Tanggal pembuatan: {datetime.now().strftime('%d-%m-%Y')}</w:t></w:r></w:p>
    <w:sectPr></w:sectPr>
  </w:body>
</w:document>'''

content_types = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>'''

rels = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>'''

now = datetime.now().strftime('%Y-%m-%dT%H:%M:%S')
core = f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>Laporan UAS Toko Sepatu</dc:title>
  <dc:creator>GitHub Copilot</dc:creator>
  <cp:created>{now}</cp:created>
  <cp:modified>{now}</cp:modified>
</cp:coreProperties>'''

app = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>Microsoft Word</Application>
</Properties>'''

with ZipFile(output, 'w', ZIP_DEFLATED) as z:
    z.writestr('[Content_Types].xml', content_types)
    z.writestr('_rels/.rels', rels)
    z.writestr('word/document.xml', content)
    z.writestr('docProps/core.xml', core)
    z.writestr('docProps/app.xml', app)

print(output)

<!DOCTYPE html>
<html>
<head>
    <title>Test Email Laravel</title>
</head>
<body>
    <h1>Hi, {{ $data['name'] ?? 'User' }}</h1>
    <p>Test Kirim Email dari Laravel</p>
    <p>Pesan: {{ $data['message'] ?? 'Tidak Ada Pesan' }}</p>
    <p>Terima kasih,</p>
    <p>{{ env('MAIL_FROM_ADDRESS') }}</p>
</body>
</html>
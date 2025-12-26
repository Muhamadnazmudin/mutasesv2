<?php
$queue_file = __DIR__ . "/wa_queue.txt";

while (true) {

    if (!file_exists($queue_file)) {
        sleep(1);
        continue;
    }

    $lines = file($queue_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    file_put_contents($queue_file, ""); // kosongkan queue

    foreach ($lines as $line) {
        $data = json_decode($line, true);

        // kirim ke server WA kamu
        $url = "ip wa kamu get kirim pesan"; //ipwakamu
        $token = ""; //token kamu

        $payload = json_encode([
            "number" => $data['number'],
            "message" => $data['message']
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "token: ".$token
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    sleep(2); // cek queue tiap 2 detik
}

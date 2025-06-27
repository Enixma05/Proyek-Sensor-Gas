<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Monitor Gas</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(to right, #6a11cb 0%, #2575fc 100%);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .dashboard-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 800px;
            width: 100%;
            color: #333;
        }
        h1 {
            color: #0056b3;
            margin-bottom: 25px;
            font-weight: 700;
        }
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .card {
            background-color: #f0f8ff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: left;
            border-left: 5px solid #007bff;
        }
        .card h3 {
            color: #007bff;
            margin-top: 0;
            margin-bottom: 15px;
            font-weight: 400;
        }
        .card .value {
            font-size: 2.2em;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        .card .timestamp {
            font-size: 0.85em;
            color: #777;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 700;
            color: #fff;
            margin-top: 10px;
            font-size: 1.1em;
        }
        .status-aman { background-color: #28a745; } /* Green */
        .status-bahaya { background-color: #dc3545; } /* Red */
        .status-silenced { background-color: #ffc107; color: #333; } /* Yellow */
        .status-unknown { background-color: #6c757d; } /* Gray */

        .control-section {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-top: 2px solid #ddd;
            margin-top: 30px;
        }
        .control-section h2 {
            color: #0056b3;
            margin-bottom: 20px;
            font-weight: 400;
        }
        .control-button {
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.1s ease;
        }
        .control-button:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        .control-button:active {
            transform: translateY(0);
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            font-weight: 500;
        }
        .message.success { background-color: #d4edda; color: #155724; }
        .message.error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Dashboard Monitoring Gas IoT</h1>
        <p>Pantau status sensor gas dari perangkat ESP32 Anda.</p>

        <div class="card-grid">
            <div class="card">
                <h3>Nilai Gas Terbaru</h3>
                <div class="value" id="gasValue">N/A</div>
                <div class="timestamp" id="lastUpdateTime">Terakhir diperbarui: N/A</div>
            </div>
            <div class="card">
                <h3>Status Alarm</h3>
                <div class="status-badge" id="alarmStatusBadge">UNKNOWN</div>
            </div>
        </div>

        <div class="control-section">
            <h2>Kontrol Alarm</h2>
            <p>Kirim perintah ke perangkat ESP32 Anda.</p>
            <form id="controlForm">
                <button type="submit" class="control-button" name="command" value="SILENCE_ALARM">Bungkam Alarm</button>
            </form>
            <div id="responseMessage" class="message" style="display:none;"></div>
        </div>
    </div>

    <script>
        // Fungsi untuk mengambil data dari last_gas_data.txt
        function fetchGasData() {
            fetch('get_gas_data.php') // Kita akan membuat file ini nanti
                .then(response => response.json())
                .then(data => {
                    document.getElementById('gasValue').textContent = data.gasValue;
                    document.getElementById('lastUpdateTime').textContent = 'Terakhir diperbarui: ' + data.timestamp;

                    const alarmBadge = document.getElementById('alarmStatusBadge');
                    alarmBadge.textContent = data.alarmStatus;
                    alarmBadge.className = 'status-badge'; // Reset class
                    if (data.alarmStatus === 'SAFE') {
                        alarmBadge.classList.add('status-aman');
                    } else if (data.alarmStatus === 'ALARM_ACTIVE') {
                        alarmBadge.classList.add('status-bahaya');
                    } else if (data.alarmStatus === 'SILENCED') {
                        alarmBadge.classList.add('status-silenced');
                    } else {
                        alarmBadge.classList.add('status-unknown');
                    }
                })
                .catch(error => {
                    console.error('Error fetching gas data:', error);
                    document.getElementById('gasValue').textContent = 'Error';
                    document.getElementById('lastUpdateTime').textContent = 'Error fetching data.';
                    document.getElementById('alarmStatusBadge').textContent = 'Error';
                    document.getElementById('alarmStatusBadge').className = 'status-badge status-unknown';
                });
        }

        // Fungsi untuk mengirim perintah kontrol
        document.getElementById('controlForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Mencegah form refresh halaman

            const command = event.submitter.value; // Ambil nilai dari tombol yang ditekan
            const formData = new FormData();
            formData.append('command', command);

            const responseDiv = document.getElementById('responseMessage');

            fetch('publish.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text()) // publish.php mengembalikan redirect, jadi kita baca teksnya
            .then(text => {
                // Karena publish.php melakukan redirect, kita perlu memparse URL-nya
                const urlParams = new URLSearchParams(text.split('?')[1]);
                const status = urlParams.get('status');
                const message = urlParams.get('message');

                responseDiv.style.display = 'block';
                if (status === 'success') {
                    responseDiv.className = 'message success';
                    responseDiv.textContent = 'Perintah berhasil dikirim!';
                } else {
                    responseDiv.className = 'message error';
                    responseDiv.textContent = 'Gagal mengirim perintah: ' + (message ? decodeURIComponent(message.replace(/\+/g, ' ')) : 'Terjadi kesalahan.');
                }
                // Sembunyikan pesan setelah beberapa detik
                setTimeout(() => { responseDiv.style.display = 'none'; }, 5000);
            })
            .catch(error => {
                console.error('Error sending command:', error);
                responseDiv.style.display = 'block';
                responseDiv.className = 'message error';
                responseDiv.textContent = 'Terjadi kesalahan koneksi saat mengirim perintah.';
                setTimeout(() => { responseDiv.style.display = 'none'; }, 5000);
            });
        });

        // Panggil fetchGasData saat halaman dimuat
        document.addEventListener('DOMContentLoaded', fetchGasData);

        // Perbarui data setiap 3 detik (polling)
        setInterval(fetchGasData, 3000);
    </script>
</body>
</html>
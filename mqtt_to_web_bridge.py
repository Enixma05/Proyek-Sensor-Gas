import paho.mqtt.client as mqtt
import requests
import json
import time

MQTT_BROKER = "broker.hivemq.com"  # Ganti jika pakai lokal
MQTT_PORT = 1883
# URL ke update_gas_data.php
WEB_SERVER_URL = "http://localhost/IoT/update_gas_data.php"

# --- UBAH BARIS INI: TAMBAHKAN 'properties' ---


def on_connect(client, userdata, flags, rc, properties):
    print(f"Connected to MQTT Broker with result code {rc}")
    # Anda bisa mencetak ini untuk debug
    print(f"Connection Properties: {properties}")
    client.subscribe("esp32/sensor/gas")
    client.subscribe("esp32/status/alarm")


def on_message(client, userdata, msg):
    print(
        f"Received MQTT message on topic: {msg.topic} with payload: {msg.payload.decode()}")
    try:
        payload_str = msg.payload.decode('utf-8')
        data_to_send = {
            "topic": msg.topic,
            "payload": payload_str
        }
        response = requests.post(WEB_SERVER_URL, json=data_to_send)
        print(f"Sent data to web server. Status: {response.status_code}")
    except Exception as e:
        print(f"Error processing MQTT message or sending to web: {e}")


# --- UBAH BARIS INI: TENTUKAN CallbackAPIVersion.VERSION2 ---
client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2, "MQTT_Web_Bridge_Ramaa")

client.on_connect = on_connect
client.on_message = on_message

print(f"Connecting to MQTT Broker: {MQTT_BROKER}:{MQTT_PORT}")
client.connect(MQTT_BROKER, MQTT_PORT, 60)
print("Entering MQTT loop...")
client.loop_forever()

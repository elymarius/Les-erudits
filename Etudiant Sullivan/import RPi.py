import RPi.GPIO as GPIO
import time

PIR_PIN = 17

GPIO.setmode(GPIO.BCM)
GPIO.setup(PIR_PIN, GPIO.IN)

print("Capteur en cours d'initialisation...")
time.sleep(10)

print("Surveillance active")

try:
    while True:
        if GPIO.input(PIR_PIN):
            print("Présence détectée")
        else:
            print("Aucune présence")

        time.sleep(1)

except KeyboardInterrupt:
    print("Programme arrêté")
    GPIO.cleanup()
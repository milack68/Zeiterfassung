import nxppy
import time
mifare = nxppy.Mifare()
# Print card UIDs as they are detected
while True:
    try:
        uid = mifare.select()
        print(uid)
	time.sleep(3)
    except nxppy.SelectError:
        # SelectError is raised if no card is in the field.
        pass

    time.sleep(0.2)
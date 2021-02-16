import nxppy
import time
import os, sys
import urllib2, time

terminal = 'http://IhreServerAdresse/terminal.php?rfid='

mifare = nxppy.Mifare()
os.system('clear')
print 'ready to read:'

while True:
	try:
		uid = mifare.select()
		time.sleep(0.1)
		if uid <> '':
			print ('\033[1;30m' + '-> verbinde mit Server .......' + '\033[1;m')
			print ('')
			try:
				info = urllib2.urlopen(terminal + uid).read()
				info = info.replace('Zeit fehlt!', '\033[1;31mZeit fehlt!\033[1;32m')
			    print ('\033[1;32m' + info + '\033[1;m')
			   	time.sleep(3)
			except:
				print ('\033[1;31m' + 'Stempel - Fehler!' + '\033[1;m')
				time.sleep(1)
				pass
		else:
			print ('\033[1;31m' + 'System- Fehler!'	+ '\033[1;m')
			time.sleep(1)		
		os.system('clear')
		print 'ready to read:'
	except nxppy.SelectError:
	        # SelectError is raised if no card is in the field.
        	pass

    	time.sleep(0.1)

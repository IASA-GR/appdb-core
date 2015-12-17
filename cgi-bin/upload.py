#!/usr/bin/python
# -*- coding: utf-8 -*-
import cgitb, cgi, os
cgitb.enable()
import sys, time

form = cgi.FieldStorage()

# Generator to buffer file chunks
def fbuffer(f, chunk_size=10000):
   while True:
      chunk = f.read(chunk_size)
      if not chunk: break
      yield chunk

def uploadFile(fileitem):
	# Test if the file was uploaded
	if fileitem.filename:

		# strip leading path from file name to avoid directory traversal attacks
		fn = os.path.basename(fileitem.filename)
		print >> file, fn
		f = open('/var/www/html/appdb/public/upload/' + fn, 'wb', 10000)

		# Read the file in chunks
		for chunk in fbuffer(fileitem.file):
			time.sleep(5)
			f.write(chunk)
		
		f.close()
	return 'The file "' + fn + '" was uploaded successfully'

file = sys.stdout

print >> file, "Content-Type: text/html"
print >> file, ""
print >> file, \
'''<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us">
    <head> 
	</head>
	<body>'''
for i in form['file']:
	print >> file, uploadFile(i)

print >> file, '''	</body>
</html>'''

file.close()

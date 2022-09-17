# -*- coding: utf-8 -*-

"""
	Copyright (C) 2022  Soheil Khodayari, CISPA
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.
	You should have received a copy of the GNU Affero General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.


	Description:
	------------
	The main program that runs the python sanitizor testing pipeline


	Usage:
	------------
	$ python3 main.py --input=/path/to/input/markups/file --output=/path/to/output/file

"""

import argparse
import os, sys
import json

# sanitizers
import bleach
import htmllaundry # .sanitize
from html_sanitizer import Sanitizer
from lxml.html.clean import clean_html

# setup a mock django app
os.environ.setdefault("DJANGO_SETTINGS_MODULE", "djangoapp.settings")
import django
django.setup()

# django html sanitizer
# see: https://github.com/ui/django-html_sanitizer/blob/master/sanitizer/tests.py
from sanitizer.templatetags.sanitizer import (sanitize as django_sanitize, sanitize_allow,
    escape_html, strip_filter, strip_html)

from django.conf import settings

def main():
	
	BASE_DIR= os.path.dirname(os.path.realpath(__file__))
	INPUT_FILE_DEFAULT = os.path.join(os.path.dirname(BASE_DIR), "markups.txt")
	OUTPUT_FILE_DEFAULT = os.path.join(BASE_DIR, "outputs/results.json")

	p = argparse.ArgumentParser(description='This script tests the python sanitizers with the given markups.')
	p.add_argument('--input', "-I",
					metavar="FILE",
					default=INPUT_FILE_DEFAULT,
					help='path to input file. (default: %(default)s)',
					type=str)

	p.add_argument('--output', "-O",
					metavar="FILE",
					default=OUTPUT_FILE_DEFAULT,
					help='path to output file. (default: %(default)s)',
					type=str)



	args= vars(p.parse_args())
	
	INPUT_FILE = args["input"]
	OUTPUT_FILE = args["output"]


	fd = open(INPUT_FILE, 'r')
	lines = fd.readlines();
	fd.close()


	results = {
		'bleach': [],
		'lxml': [],
		'htmlsanitizer': [],
		'htmllaundry': [],
		'django-html-sanitizer': [],
		'django-html-escape': []
	}

	# # instantiate 
	htmlSanitizor = Sanitizer()

	for line in lines:
		payload = line.strip().rstrip('\n').strip()


		output = bleach.clean(payload)
		try:
			results['bleach'].append({
				'input': payload,
				'output': output
			})
		except:
			results['bleach'].append({
				'input': payload,
				'output': 'error'
			})

		try:
			output = htmlSanitizor.sanitize(payload)
			results['htmlsanitizer'].append({
				'input': payload,
				'output': output
			})
		except:
			results['htmlsanitizer'].append({
				'input': payload,
				'output': 'error'
			})

		try:
			output = htmllaundry.sanitize(payload)
			results['htmllaundry'].append({
				'input': payload,
				'output': output
			})
		except:
			results['htmllaundry'].append({
				'input': payload,
				'output': 'error'
			})	

		try:
			output = clean_html(payload)
			results['lxml'].append({
				'input': payload,
				'output': output
			})
		except:
			results['lxml'].append({
				'input': payload,
				'output': 'error'
			})


		try:
			output = django_sanitize(payload)
			results['django-html-sanitizer'].append({
				'input': payload,
				'output': output
			})
		except:
			results['django-html-sanitizer'].append({
				'input': payload,
				'output': 'error'
			})	

		try:
			output = escape_html(payload, allowed_tags=settings.SANITIZER_ALLOWED_TAGS, allowed_attributes=settings.SANITIZER_ALLOWED_ATTRIBUTES, allowed_styles=settings.SANITIZER_ALLOWED_STYLES)
			results['django-html-escape'].append({
				'input': payload,
				'output': output
			})
		except:
			results['django-html-escape'].append({
				'input': payload,
				'output': 'error'
			})	


	with open(OUTPUT_FILE, 'w+', encoding='utf-8') as fd:
		json.dump(results, fd, ensure_ascii=False, indent=4)



if __name__ == "__main__":
	main()







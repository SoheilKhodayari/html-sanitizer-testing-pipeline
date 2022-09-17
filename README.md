# Sanitizer Testing Pipeline

[![Open Source Love](https://badges.frapsoft.com/os/v1/open-source.svg?v=103)](https://github.com/ellerbrock/open-source-badges/) [![Tweet](https://img.shields.io/twitter/url/http/shields.io.svg?style=social)](https://twitter.com/intent/tweet?text=Sanitizer-Testing-Pipeline&url=https://soheilkhodayari.github.io/html-sanitizers-testing-pipeline/)

A collection of scripts to test popular HTML sanitizers of different programming languages.

## Prerequosites

- Python3, and pip
- PHP, and composer
- C#, .NET, and nuget
- Node.js and npm
- Java and Maven

## Setup

Install the necessary dependencies:

```bash
$ (cd clientside && pip3 install -r requirements.txt)
$ (cd php && composer update)
$ (cd node-js && npm install)
$ (cd python && pip3 install -r requirements.txt)
$ (cd chsarp && nuget restore)
$ (cd csharp/Sanitizer && nuget install packages.config)
$ (cd java && mvn install)
$ (cd java && mvn install:install-file -Dfile=./libs/htmlcleaner-2.24.jar -DgroupId=org.htmlcleaner -DartifactId=htmlcleaner -Dversion=2.24 -Dpackaging=jar -DgeneratePom=true)
```

or simply:

```bash
$ ./install.sh
```

## Running

You can run sanitizers of each programming language under its own setup, as detailed below. All outputs will be available in the `outputs` directory of the respective language. 


### Client-side JS

First, run the backend webserver hosting the webpage with sanitizer tests:
```
$ cd clientside
$ python3 manage.py runserver 8000
```

Then, simply visit `http://127.0.0.1:8000` in your web browser. 

Within a few moments, you can see the results in the `clientside/outputs` folder. 


**Note.** Change the default input/output parameters in `clientside/tests/sanitize.html`.


### Python

```bash
$ cd python

$ python3 main.py --input=/path/to/input/markups/file --output=/path/to/output/file
$ python3 main.py -h

usage: main.py [-h] [--input FILE] [--output FILE]

This script tests the python sanitizers with the given markups.

options:
  -h, --help              show this help message and exit
  --input FILE, -I FILE   path to input file. (default: ./../markups.txt)
  --output FILE, -O FILE  path to output file. (default: ./outputs/results.json)
````


### PhP

```bash
$ cd php
# CLI args
$ php main.php /path/to/input/markups/file /path/to/output/file

# defaults: input: ./../markups.txt, output: ./outputs/results.json
$ php main.php 
````


### Node.js

```bash
$ cd node-js
# CLI args
$ node main.js --input=/path/to/input/markups/file --output=/path/to/output/file
# defaults: input: ./../markups.txt, output: ./outputs/results.json
$ node main.js
```


### Java 

Run the main class: `src.java.com.sanitizer.Main.java`.

For example, you can use Maven:

```bash
$ mvn exec:java
```

### C#

Run the `Sanitizer.sln` solution file in Visual Studio.

Alternatively, compile the `Sanitizer/Program.cs` with `csc` and run it with [`mono`](https://www.mono-project.com/docs/getting-started/mono-basics/). 

```bash
$ mono Program.exe
```

## License

This project is available as open source under the terms of the `GNU AFFERO GENERAL PUBLIC LICENSE V3.0`. 

You may not use this program except in compliance with the license. This program is distributed on an "AS IS" BASIS in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 

See [LICENSE](LICENSE) for more information.



## Academic Publication

The contents of this repository has been developed as a part of a IEEE SP'23 work. If you use it for academic research, we encourage you to cite our [paper](https://publications.cispa.saarland/3756/). For more information, visit [https://domclob.xyz](https://domclob.xyz).

```
@inproceedings {SKhodayariSP23TheThing,
  author = {Soheil Khodayari and Giancarlo Pellegrino},
  title = {It's (DOM) Clobbering Time: Attack Techniques, Prevalence, and Defenses,
  booktitle = {To Appear at proceedings of the 44rd IEEE Symposium on Security and Privacy},
  year = {2023},
}
```
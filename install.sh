#!/usr/bin/env bash

# clientside JS sanitizer dependencies
(cd clientside && pip3 install -r requirements.txt)

# php sanitizer dependencies
(cd php && composer update)

# node.js sanitizer dependencies
(cd node-js && npm install)

# python sanitizer dependencies
(cd python && pip3 install -r requirements.txt)

# c# assemblies
(cd chsarp && nuget restore)
(cd csharp/Sanitizer && nuget install packages.config)

# java dependencies
(cd java && mvn install)
(cd java && mvn install:install-file -Dfile=./libs/htmlcleaner-2.24.jar -DgroupId=org.htmlcleaner -DartifactId=htmlcleaner -Dversion=2.24 -Dpackaging=jar -DgeneratePom=true)
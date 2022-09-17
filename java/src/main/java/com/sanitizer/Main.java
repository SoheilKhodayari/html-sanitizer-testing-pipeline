package com.sanitizer;


import org.htmlcleaner.CleanerProperties;
import org.htmlcleaner.HtmlCleaner;
import org.htmlcleaner.SimpleHtmlSerializer;
import org.htmlcleaner.TagNode;
import org.htmlcleaner.Serializer;

import org.jsoup.Jsoup;
import org.jsoup.safety.Whitelist;
import org.owasp.html.PolicyFactory;
import org.owasp.html.Sanitizers;
import org.owasp.validator.html.AntiSamy;
import org.owasp.validator.html.CleanResults;
import org.owasp.validator.html.Policy;
import org.owasp.validator.html.PolicyException;
import org.owasp.validator.html.ScanException;

import java.io.*;
import java.util.*;
import java.nio.channels.FileChannel;
import java.nio.file.Paths;
import java.nio.file.StandardOpenOption;

import com.google.gson.Gson;
import com.google.gson.GsonBuilder;


public class Main {

    public static void main(String[] args) throws IOException, ScanException, PolicyException {

        String INPUT_PATH_WITHIN_RESOURCES = "markups.txt";
        String OUTPUT_PATH = "outputs/results.json";

        if(args.length >=2){
            INPUT_PATH_WITHIN_RESOURCES = args[0];
            OUTPUT_PATH = args[1];
        }

        List<String> payloads = new ArrayList<>();

        ClassLoader classloader = Thread.currentThread().getContextClassLoader();
        InputStream markupPath = classloader.getResourceAsStream(INPUT_PATH_WITHIN_RESOURCES);
        try {
            Scanner scanner = new Scanner(markupPath);
            String p;
            boolean DEBUG = false;
            while (scanner.hasNextLine()) {
                p = scanner.nextLine();
                payloads.add(p);
                if (DEBUG) {
                    System.out.println(p);
                }
            }
            scanner.close();
        } catch (Exception e) {
            e.printStackTrace();
        }

        String input;
        String output;
        Map<String, String> iterator;

        Map<String, ArrayList<Map<String, String>>> results = new HashMap<String, ArrayList<Map<String, String>>>();
        results.put("java-html-sanitizer", new ArrayList<Map<String, String>>());
        results.put("antisamy", new ArrayList<Map<String, String>>());
        results.put("html-cleaner", new ArrayList<Map<String, String>>());
        results.put("jsoup", new ArrayList<Map<String, String>>());


        // java html sanitizer
        PolicyFactory policyJavaSanitizer = Sanitizers.FORMATTING.and(Sanitizers.LINKS);

        // antisamy
        Policy policyAntisamy = Policy.getInstance("src/main/resources/antisamy-default.xml");
        AntiSamy antiSamyInstance = new AntiSamy();


        // HtmlCleaner
        HtmlCleaner htmlCleaner = new HtmlCleaner();
        CleanerProperties htmlCleanerProps = htmlCleaner.getProperties();
        final SimpleHtmlSerializer htmlSerializer = new SimpleHtmlSerializer(htmlCleanerProps);


        // loop through payloads 
        for (int i = 0; i < payloads.size(); i++) {

            iterator = new HashMap<String, String>();
            input = payloads.get(i);
            iterator.put("input", input);

            // Jsoup
            output = Jsoup.clean(input, Whitelist.basic());
            iterator.put("output", output);
            results.get("jsoup").add(iterator);

            // java sanitizer
            output = policyJavaSanitizer.sanitize(input);
            iterator.put("output", output);
            results.get("java-html-sanitizer").add(iterator);


            // antisamy
            CleanResults cleaned = antiSamyInstance.scan(input, policyAntisamy);
            output = cleaned.getCleanHTML();
            iterator.put("output", output);
            results.get("antisamy").add(iterator);


            // html cleaner
            TagNode node = htmlCleaner.clean(input);
            output = htmlSerializer.getAsString(node, htmlCleanerProps.getCharset());
            iterator.put("output", output);
            results.get("html-cleaner").add(iterator);

        }

        // store the results in a json

        GsonBuilder gbuilder = new GsonBuilder();
        gbuilder.setPrettyPrinting();
        gbuilder.disableHtmlEscaping();
        Gson gsonSerializer = gbuilder.create();

        Writer writer = new FileWriter(OUTPUT_PATH);
        gsonSerializer.toJson(results, writer);

        writer.close();

    }


}

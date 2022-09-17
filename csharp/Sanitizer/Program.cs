using AjaxControlToolkit.HtmlEditor.Sanitizer;
using System;
using System.Collections.Generic;
using System.Web.Security.AntiXss;
using Vereyon.Web;
using Newtonsoft.Json;
using NSoup;

namespace Sanitizer
{
    class Program
    {
        static void Main(string[] args)
        {

            var INPUT_FILE = @"../../../../markups.txt";
            var OUTPUT_FILE = @"../../../outputs/results.json";

            if (args.Length > 0 && args.Length >= 2)
            {
                INPUT_FILE = args[0];
                OUTPUT_FILE = args[1];
            }


            List<String> payloads = new List<String>();
            string line;

            System.IO.StreamReader file = new System.IO.StreamReader(INPUT_FILE);
            while ((line = file.ReadLine()) != null)
            {
                payloads.Add(line);
            }


            Dictionary<string, List< Dictionary<string, string>>> results = new Dictionary<string, List<Dictionary<string, string>>>();
            results.Add("GanssHtmlSanitizer", new  List<Dictionary<string, string>>() );
            results.Add("AntiXssEncoder", new List<Dictionary<string, string>>());
            results.Add("AJAXToolkit", new List<Dictionary<string, string>>());
            results.Add("NSoup", new List<Dictionary<string, string>>());
            results.Add("HtmlRuleSanitizer", new List<Dictionary<string, string>>());

            string input;
            string output;
            

            var ganssHtmlSanitizer = new Ganss.XSS.HtmlSanitizer();
            DefaultHtmlSanitizer ajaxToolKitSanitizer = new DefaultHtmlSanitizer();
            var htmlRuleSanitizer = HtmlSanitizer.SimpleHtml5Sanitizer();

            Dictionary<string, string> iterator;
            for (int i=0; i<payloads.Count; i++)
            {
                input = payloads[i];


                // ganssHtmlSanitizer
                output = ganssHtmlSanitizer.Sanitize(input);
                iterator = new Dictionary<string, string>();
                iterator.Add("input", input);
                iterator.Add("output", output);
                results["GanssHtmlSanitizer"].Add(iterator);

                // AntiXssEncoder
                output = AntiXssEncoder.HtmlEncode(input, true);
                iterator = new Dictionary<string, string>();
                iterator.Add("input", input);
                iterator.Add("output", output);
                results["AntiXssEncoder"].Add(iterator);

                // AJAXToolkit
                output = ajaxToolKitSanitizer.GetSafeHtmlFragment(input, new Dictionary<string, string[]>());
                iterator = new Dictionary<string, string>();
                iterator.Add("input", input);
                iterator.Add("output", output);
                results["AJAXToolkit"].Add(iterator);

                // HtmlRuleSanitizer
                output = htmlRuleSanitizer.Sanitize(input);
                iterator = new Dictionary<string, string>();
                iterator.Add("input", input);
                iterator.Add("output", output);
                results["HtmlRuleSanitizer"].Add(iterator);

                // NSoup
                output = NSoup.NSoupClient.Clean(input, NSoup.Safety.Whitelist.Basic);
                iterator = new Dictionary<string, string>();
                iterator.Add("input", input);
                iterator.Add("output", output);
                results["NSoup"].Add(iterator);


            }


            string serializedOutput = JsonConvert.SerializeObject(results, Formatting.Indented);
            System.IO.File.WriteAllText(OUTPUT_FILE, serializedOutput);
               

        }

        
    }
}

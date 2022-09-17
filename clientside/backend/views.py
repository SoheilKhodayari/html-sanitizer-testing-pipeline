from django.conf import settings
from django.shortcuts import redirect, resolve_url, render
from django.urls import reverse
from django.views.decorators.http import require_GET,require_POST
from django.http import HttpResponse, HttpResponseRedirect, JsonResponse
from django.views.decorators.csrf import csrf_exempt
import os, json


@require_GET
def get_sanitizer_test(request):

	context = {'markup': '{{ markup }}'}
	return render(request, "sanitize.html", context)


@csrf_exempt
@require_POST
def post_tests_results(request):

	query_params = dict(request.GET)

	body_unicode = request.body.decode('utf-8')
	body = json.loads(body_unicode)
	data = body

	results_folder = os.path.join(settings.OUTPUT_DIR)
	if not os.path.exists(results_folder):
		os.makedirs(results_folder)

	test_file = os.path.join(results_folder, 'results.json')
	with open(test_file, 'w+') as fd:
		json.dump(data, fd, indent=4)

	return JsonResponse({"OperationStatus": 200 })
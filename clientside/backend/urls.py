from django.conf.urls import url
from . import views

app_name = 'sanitizer-app'
urlpatterns = [
     url(r'^test/$', views.get_sanitizer_test, name='get_sanitizer_test'),
     url(r'^post-test/$', views.post_tests_results, name='post_sanitizer_test'),
]
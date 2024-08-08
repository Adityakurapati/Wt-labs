from django.urls import path
from . import views

urlpatterns = [
    path('', views.calculate_electricity_bill, name='calculate_electricity_bill'),
]
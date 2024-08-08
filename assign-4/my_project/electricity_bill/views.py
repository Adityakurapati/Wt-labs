from django.shortcuts import render
from django.http import JsonResponse

def calculate_electricity_bill(request):
    if request.method == 'POST':
        units = int(request.POST.get('units'))
        bill_amount = calculate_bill(units)
        return JsonResponse({'bill_amount': bill_amount})
    return render(request, 'electricity_bill/index.html')

def calculate_bill(units):
    if units <= 50:
        bill_amount = units * 3.5
    elif units <= 150:
        bill_amount = 50 * 3.5 + (units - 50) * 4.0
    elif units <= 250:
        bill_amount = 50 * 3.5 + 100 * 4.0 + (units - 150) * 5.2
    else:
        bill_amount = 50 * 3.5 + 100 * 4.0 + 100 * 5.2 + (units - 250) * 6.5
    return bill_amount
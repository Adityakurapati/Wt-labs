$(document).ready(function() {
        $('#bill-form').submit(function(event) {
          event.preventDefault();
          var units = $('#units').val();
          $.ajax({
            type: 'POST',
            url: '/',
            data: {
              units: units,
              csrfmiddlewaretoken: $('input[name=csrfmiddlewaretoken]').val()
            },
            success: function(data) {
              $('#result').html(`Your electricity bill is Rs. ${data.bill_amount.toFixed(2)}`);
            },
            error: function() {
              $('#result').html('Error calculating electricity bill.');
            }
          });
        });
      });

$(document).ready(function() {
  function formatNumberTo9Digits(number) {
    // Convert number to string
    let numberStr = String(number);
    
    // Calculate number of leading zeros needed
    let leadingZeros = '000000000'.substring(0, 9 - numberStr.length);
    
    // Concatenate leading zeros with the number
    return leadingZeros + numberStr;
  }
  function removeTimeFromDate(datetime) {
    // Split the datetime string into date and time parts
    let parts = datetime.split(' ');

    // Return only the date part (first part)
    return parts[0];
  }


  $('.checkoutButton').click(function() {
    var tableId = $(this).data('table-id');
    var billingId = $(this).data('billing-id');
    var formatId = (tableId < 10) ? '0' + tableId : tableId;

    $('#checkoutForm').attr('method', 'POST');
    $('#checkoutForm').attr('action', 'proses/deal_billing.php?id=' + billingId);
    $('#checkoutModalNumber').html('<div class="card-title numbered-box signature-box">' + formatId + '</div>');

    // AJAX request to get billing data
    var currentDay =  new Date().getDay(); // 0 (for Sunday) through 6 (for Saturday)
    var billingDataPaket = "Weekday";
    if (currentDay == 0 || currentDay == 6) { // Sunday or Saturday
      billingDataPaket = "Weekend";
    }
    $.ajax({
        url: '/SistemBilliard/proses/get_data_billing_history.php',
        type: 'POST',
        data: { billing_id: billingId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                var billingData = response.data;
                $('#tanggal').text(removeTimeFromDate(billingData.waktu_mulai));
                $('#no_faktur').text(formatNumberTo9Digits(billingData.billing_id)); // Adjust this if necessary
                $('#paket').text(billingDataPaket); // Adjust this if necessary
                $('#table').text(billingData.no_meja);
                $('#mulai').text(billingData.waktu_mulai);
                $('#selesai').text(billingData.waktu_selesai);
                $('#durasi').text(billingData.durasi);
                $('#harga_table').text(Number(billingData.harga_perjam).toLocaleString('id-ID') + "/jam");
                $('#subtotal_table').text(Number(billingData.harga).toLocaleString('id-ID'));

                $('#nama_tamu').val(billingData.nama_penyewa);

                // AJAX request to get fnb orders data
                $.ajax({
                    url: '/SistemBilliard/proses/get_fnb_orders.php',
                    type: 'GET',
                    data: { billing_id: billingId },
                    dataType: 'json',
                    success: function(fnbResponse) {
                        var fnbDetailsHtml = '';
                        var fullTotal = 0;
                        fnbResponse.items.forEach(function(item) {
                            fullTotal += Number(item.total_fnb);

                            fnbDetailsHtml += '<tr>';
                            fnbDetailsHtml += '<td style="width: 35%;">' + item.nama_fnb + '</td>';
                            fnbDetailsHtml += '<td style="width: 25%;">' + Number(item.harga_fnb).toLocaleString('id-ID') + '</td>';
                            fnbDetailsHtml += '<td style="width: 15%;">' + item.jumlah_fnb + '</td>';
                            fnbDetailsHtml += '<td style="width: 25%;">' + Number(item.total_fnb).toLocaleString('id-ID') + '</td>';
                            fnbDetailsHtml += '</tr>';
                        });
                        $('#totalFnbCheckout').text(fullTotal.toLocaleString('id-ID'));
                        $('#fnb-details').html(fnbDetailsHtml);

                        fullTotal += Number(billingData.harga);
                        $('#grand_total').val(fullTotal.toLocaleString('id-ID'));
                        $('#uang_diterima').val(fullTotal.toLocaleString('id-ID'));
                        updateChange();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching FNB orders data:', status, error);
                    }
                });
            } else {
                console.error('Error fetching billing data:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching billing data:', status, error);
        }
    });
  });


// Event listener for 'Uang diterima' input field
$('#uang_diterima').on('input', function () {
  input = $(this);
  // Remove non-numeric characters
  let value = input.val().replace(/[^0-9]/g, '');

  // Null handler
  if (value) {
    // Convert to float
    let floatValue = parseFloat(value);

    // Ensure the minimum value is 0
    floatValue = Math.max(0, floatValue);

    // Format number
    let formattedValue = floatValue.toLocaleString('id-ID');

    // Update input value
    input.val(formattedValue);
  } else {
    input.val(value);
  }

  // Update the 'Uang kembalian' field
  updateChange();
});

// Function to update the change value
function updateChange() {
    var grandTotal = $('#grand_total').val().replace(/[^0-9]/g, '');
    var uangDiterima = $('#uang_diterima').val().replace(/[^0-9]/g, '');

    if (isNaN(uangDiterima)) {
        uangDiterima = 0;
    }

    var uangKembalian = uangDiterima - grandTotal;
    // if (uangKembalian < 0) {
    //     $('#checkoutForm').html('<button type="submit" class="btn btn-secondary disable-hover"><i class="fa fa-check" aria-hidden="true"></i> Deal</button>');
    // } else {
    //     $('#checkoutForm').html('<button type="submit" class="btn btn-success"><i class="fa fa-check" aria-hidden="true"></i> Deal</button>');
    // }
    // $('#uang_kembalian').val(uangKembalian.toLocaleString('id-ID'));
    var buttonClass = (uangKembalian < 0) ? 'btn btn-secondary disable-hover' : 'btn btn-success';
    $('#checkoutForm button[type="submit"]').attr('class', buttonClass);
    
}

});

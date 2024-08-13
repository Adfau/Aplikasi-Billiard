<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] != "ADMIN") {
    header( "Location: signin.php" );
    exit();
  }

require_once('controller.php');
define('INCLUDED', true);
define('PAGE_REPORT', true);
?>


<!DOCTYPE html>
<html lang="en">
<?php include("head.php"); ?>
<head>
<!-- Include Required Prerequisites -->
<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
 
<!-- Include Date Range Picker -->
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
<style>
.graph-container {
    margin: 0 auto;
}

.report-table {
    width: 100%;
    background-color: #f8f8f8;
}
.salesReportBilling {
  width: 100%;
}

</style>
</head>
<body>
    <div class="container-fluid">
        <div class="row no-padding">
            <?php include("header.php"); ?>
            <div class="col no-padding d-flex">
                <?php include("sidebar-admin.php"); ?>
                <div class="container main-content mt-5">
                    <div class="row justify-content-center">
                        <!-- Main Content -->
                        <div class="col-12">
                            <h1>Data Penjualan</h1>
                            <hr>

                        <!-- Summary Section -->
                        <div class="col-12 mb-4">
                            <div class="row text-center">
                                <div class="col">
                                    <h2 id="totalPenghasilan">Rp. 0</h2>
                                    <p class="text-muted">Total Penghasilan</p>
                                </div>
                                <div class="col">
                                    <h2 id="totalDurasi">0 Jam</h2>
                                    <p class="text-muted">Total Durasi</p>
                                </div>
                                <div class="col">
                                    <h2 id="totalPelanggan">0</h2>
                                    <p class="text-muted">Total Pelanggan</p>
                                </div>
                            </div>
                        </div>

                        <h2>Data Penjualan Billing</h2>

<div class="salesReportBilling">
    <!-- <div id="toolbar">
    <button id="remove" class="btn btn-danger" disabled>
        <i class="fa fa-trash"></i> Delete
    </button>
    </div> -->
    <table
    id="table"
    class="report-table"
    data-toolbar="#toolbar"
    data-search="true"
    data-show-refresh="true"
    data-show-columns="true"
    data-show-columns-toggle-all="true"
    data-show-export="true"
    data-click-to-select="true"
    data-minimum-count-columns="2"
    data-show-pagination-switch="true"
    data-pagination="true"
    data-id-field="id"
    data-page-list="[10, 25, 50, 100, all]"
    data-show-footer="true"
    data-side-pagination="server"
    data-url="proses/get_data_sales_billing.php"
    data-response-handler="responseHandler">
    </table>

    <script>
        var $table = $('#table')
        var $remove = $('#remove')
        var selections = []

        function getIdSelections() {
            return $.map($table.bootstrapTable('getSelections'), function (row) {
                return row.id
            })
        }

        function responseHandler(res) {
            $.each(res.rows, function (i, row) {
                row.state = $.inArray(row.id, selections) !== -1
            })
            return res
        }

        function operateFormatter(value, row, index) {
            return [
                '<a class="like" href="javascript:void(0)" title="Like">',
                '<i class="fa fa-heart"></i>',
                '</a>  ',
                '<a class="remove" href="javascript:void(0)" title="Remove">',
                '<i class="fa fa-trash"></i>',
                '</a>'
            ].join('')
        }

        window.operateEvents = {
        'click .like': function (e, value, row, index) {
            alert('You click like action, row: ' + JSON.stringify(row))
        },
        'click .remove': function (e, value, row, index) {
            $table.bootstrapTable('remove', {
            field: 'id',
            values: [row.id]
            })
        }
        }

        function totalTextFormatter(data) {
            return 'Total'
        }

        function totalNameFormatter(data) {
            return data.length
        }

        function blankFormatter(data) {
            return '5';
        }

        function totalPriceFormatter(data) {
            var field = this.field
            return 'Rp. ' + data.map(function (row) {
                return +row[field]
            }).reduce(function (sum, i) {
                return sum + i
            }, 0).toLocaleString('id-ID');
        }

        function totalDurationFormatter(data) {
        var field = this.field;  // Get the field name for the duration
        var totalSeconds = data.map(function (row) {
            var parts = row[field].split(':');
            return (+parts[0]) * 3600 + (+parts[1]) * 60 + (+parts[2]);
        }).reduce(function (sum, seconds) {
            return sum + seconds;
        }, 0);

        // Convert total seconds back to HH:mm:ss format
        var hours = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;

        return pad(hours, 2) + ':' + pad(minutes, 2) + ':' + pad(seconds, 2);
        }

        // Helper function to pad numbers with leading zeros
        function pad(number, length) {
            var str = '' + number;
            while (str.length < length) {
                str = '0' + str;
            }
            return str;
        }

        function initTable() {
          $table.bootstrapTable('destroy').bootstrapTable({
              height: 550,
              locale: 'id-ID',
              columns: [
              // {
              //     field: 'state',
              //     checkbox: true,
              //     align: 'center',
              //     valign: 'middle'
              // },
              {
                  field: 'billing_id',
                  title: 'ID',
                  align: 'center',
                  valign: 'middle',
                  sortable: true,
                  footerFormatter: totalTextFormatter
              },
              {
                  field: 'nama_penyewa',
                  title: 'Nama Pelanggan',
                  align: 'center',
                  valign: 'middle',
                  sortable: true,
                  footerFormatter: totalNameFormatter
              },
              {
                  field: 'waktu_mulai',
                  title: 'Mulai',
                  align: 'center',
                  valign: 'middle',
                  sortable: true,
              },
              {
                  field: 'waktu_selesai',
                  title: 'Selesai',
                  align: 'center',
                  valign: 'middle',
                  sortable: true,
              },
              {
                  field: 'durasi',
                  title: 'Durasi',
                  align: 'center',
                  valign: 'middle',
                  sortable: true,
                  footerFormatter: totalDurationFormatter
              },
              {
                  field: 'no_meja',
                  title: 'No. Meja',
                  align: 'center',
                  valign: 'middle',
                  sortable: true,
              },
              {
                  field: 'harga',
                  title: 'Harga',
                  align: 'center',
                  valign: 'middle',
                  sortable: true,
                  footerFormatter: totalPriceFormatter
              }
              ]
          })
          $table.on('check.bs.table uncheck.bs.table ' +
              'check-all.bs.table uncheck-all.bs.table',
          function () {
              $remove.prop('disabled', !$table.bootstrapTable('getSelections').length)

              selections = getIdSelections()
          })
          $table.on('all.bs.table', function (e, name, args) {
              console.log(name, args)
          })
          $remove.click(function () {
              var ids = getIdSelections()
              $table.bootstrapTable('remove', {
              field: 'id',
              values: ids
              })
              $remove.prop('disabled', true)
          })
        }

        $(function() {
            initTable();
        })
    </script>
</div>

                        <div style="margin-bottom: 40px;"></div>

                        <h2>Data Penjualan FnB</h2>

<div class="salesReportBilling">
  <table
    id="table2"
    class="report-table"
    data-toolbar="#toolbar"
    data-search="true"
    data-show-refresh="true"
    data-show-columns="true"
    data-show-columns-toggle-all="true"
    data-show-export="true"
    data-click-to-select="true"
    data-minimum-count-columns="2"
    data-show-pagination-switch="true"
    data-pagination="true"
    data-id-field="id"
    data-page-list="[10, 25, 50, 100, all]"
    data-show-footer="true"
    data-side-pagination="server"
    data-url="proses/get_data_sales_fnb.php"
    data-response-handler="responseHandler">
  </table>

  <script>
    var $table2 = $('#table2');
    var $remove2 = $('#remove2');
    var selections2 = [];

    function totalAmountFormatter(data) {
        var field = this.field
        return data.map(function (row) {
            return +row[field]
        }).reduce(function (sum, i) {
            return sum + i
        }, 0);
    }

    function initTable2() {
      $table2.bootstrapTable('destroy').bootstrapTable({
          height: 550,
          locale: 'id-ID',
          columns: [
          // {
          //     field: 'state',
          //     checkbox: true,
          //     align: 'center',
          //     valign: 'middle'
          // },
          {
              field: 'id_order',
              title: 'ID',
              align: 'center',
              valign: 'middle',
              sortable: true,
              footerFormatter: totalTextFormatter
          },
          {
              field: 'id_billing',
              title: 'Billing ID',
              align: 'center',
              valign: 'middle',
              sortable: true
          },
          {
              field: 'nama_fnb',
              title: 'Menu',
              align: 'center',
              valign: 'middle',
              sortable: true,
              footerFormatter: totalNameFormatter
          },
          {
              field: 'jumlah_fnb',
              title: 'Jumlah',
              align: 'center',
              valign: 'middle',
              sortable: true,
              footerFormatter: totalAmountFormatter
          },
          {
              field: 'harga_fnb',
              title: 'Harga',
              align: 'center',
              valign: 'middle',
              sortable: true,
              footerFormatter: totalPriceFormatter
          },
          {
              field: 'timestamp',
              title: 'Timestamp',
              align: 'center',
              valign: 'middle',
              sortable: true
          }
          ]
      })
      $table2.on('check.bs.table uncheck.bs.table ' +
          'check-all.bs.table uncheck-all.bs.table',
      function () {
          $remove2.prop('disabled', !$table2.bootstrapTable('getSelections').length)

          selections2 = getIdSelections()
      })
      $table2.on('all.bs.table', function (e, name, args) {
          console.log(name, args)
      })
    }

    $(function() {
        initTable2();
    })
  </script>
</div>

                            <!-- Line and Bar Graphs -->
                            <div class="container mt-5">
                                <h2>Grafik Penjualan</h2>
                                <hr>
                                <div class="row">
                                    <div class="col-md-8 graph-container">
                                        <canvas id="comboChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- <button id="exportButton" class="btn btn-success mt-4">Export to Excel</button> -->
                            <div style="margin-bottom: 20px;"></div>
                        </div>
                    </div>
                </div>
                <div style="margin-left: 40px;"></div>
            </div>
        </div>
    </div>
    
    <?php
        function displayTimer() {
            // JavaScript code to update countdown dynamically
            $jsCode = "<script>";
            $timeZone = date_default_timezone_get();
            $jsCode .= "let options = {timeZone: '$timeZone'};";
            $jsCode .= "var textWaktu = 'Sisa Waktu: ';";

            $jsCode .= "function updateTimer() {";

                //Loop
                $jsCode .= "requestAnimationFrame(updateTimer);";
                
                //Live Clock
                $jsCode .= "var now = new Date();";
                $jsCode .= "var datetimeElement = document.getElementById('datetime');";
                $jsCode .= "datetimeElement.textContent = now.toLocaleString('en-GB', options);";
                
            $jsCode .= "}";

            $jsCode .= "updateTimer();"; //Update every frame

            $jsCode .= "</script>";
            
            // Output JavaScript code
            echo $jsCode;
        }

        displayTimer();

    ?>
    
    <!-- JavaScript to Calculate Totals -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
          totalKeseluruhan = 0;

          fetchBillingData();
          fetchFnbData();
          
          function fetchBillingData() {
              $.ajax({
                  url: 'proses/get_data_sales_billing.php',
                  type: 'POST',
                  dataType: 'json',
                  success: function(data) {
                      updateSummary(data.rows);
                  },
                  error: function(error) {
                      console.error('Error fetching billing data:', error);
                  }
              });
          }

          function fetchFnbData() {
              $.ajax({
                  url: 'proses/get_data_sales_fnb.php',
                  type: 'POST',
                  dataType: 'json',
                  success: function(data) {
                      calculateTotals(data.rows);
                  },
                  error: function(error) {
                      console.error('Error fetching F&B data:', error);
                  }
              });
          }

          function updateSummary(rows) {
              let totalPenghasilan = 0;
              let totalDurasi = 0;
              let totalPelanggan = rows.length;

              rows.forEach(row => {
                  const durasi = convertDurationToMinutes(row.durasi);
                  const harga = row.harga;

                  totalDurasi += durasi / 60;
                  totalPenghasilan += harga;
                  totalKeseluruhan += harga;
              });

              //document.getElementById('totalPenghasilan').textContent = 'Rp. ' + totalPenghasilan.toLocaleString('id-ID');
              document.getElementById('totalDurasi').textContent = totalDurasi.toFixed(0) + ' Jam';
              document.getElementById('totalPelanggan').textContent = totalPelanggan;
              // document.getElementById('totalDurasiFoot').textContent = totalDurasi + ' Menit';
              // document.getElementById('totalHargaFoot').textContent = 'Rp. ' + totalPenghasilan.toLocaleString('id-ID');
          }

          function calculateTotals(rows) {
              let totalJumlah = 0;
              let totalPendapatan = 0;

              rows.forEach(row => {
                  const jumlah = row.jumlah_fnb;
                  const hargaTotal = row.total_fnb;

                  totalJumlah += jumlah;
                  totalPendapatan += hargaTotal;
                  totalKeseluruhan += hargaTotal;
              });

              // document.getElementById('totalJumlah').textContent = totalJumlah;
              // document.getElementById('totalPendapatan').textContent = 'Rp. ' + totalPendapatan.toLocaleString('id-ID');
              document.getElementById('totalPenghasilan').textContent = 'Rp. ' + totalKeseluruhan.toLocaleString('id-ID');
          }

          function convertDurationToMinutes(duration) {
              const parts = duration.split(':');
              const hours = parseInt(parts[0], 10);
              const minutes = parseInt(parts[1], 10);
              const seconds = parseInt(parts[2], 10);
              return hours * 60 + minutes + seconds / 60;
          }
      });

    </script>

    <!-- Include Chart.js -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->


    <script>
        async function fetchData() {
            const response = await fetch('proses/get_data_sales_all.php'); // Update with the correct path to your PHP script
            const data = await response.json();
            return data;
        }

        function aggregateMonthlyData(data, key) {
            const monthlyData = {};
            data.forEach(item => {
                const date = new Date(item.timestamp);
                const month = date.toLocaleString('default', { month: 'short' });
                if (!monthlyData[month]) {
                    monthlyData[month] = 0;
                }
                monthlyData[month] += parseFloat(item[key]);
            });
            return monthlyData;
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const data = await fetchData();

            const billingData = aggregateMonthlyData(data.billingData, 'harga');
            const fnbData = aggregateMonthlyData(data.fnbData, 'total_fnb');

            const labels = Object.keys(billingData);
            const billingValues = Object.values(billingData);
            const fnbValues = Object.values(fnbData);

            const ctxCombo = document.getElementById('comboChart').getContext('2d');

            new Chart(ctxCombo, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Billing Data',
                            data: billingValues,
                            backgroundColor: 'rgba(153, 102, 255, 0.2)',
                            borderColor: 'rgba(153, 102, 255, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'FnB Data',
                            data: fnbValues,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    
    <!-- Export to Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.15.1/xlsx.full.min.js"></script>
    <script>
        document.getElementById('exportButton').addEventListener('click', function() {
            const table1 = document.querySelector('#tableBody').parentNode;
            const table2 = document.querySelector('#fnbTableBody').parentNode;
            
            const wb = XLSX.utils.book_new();
            const ws1 = XLSX.utils.table_to_sheet(table1);
            const ws2 = XLSX.utils.table_to_sheet(table2);
            
            XLSX.utils.book_append_sheet(wb, ws1, 'Billing Data');
            XLSX.utils.book_append_sheet(wb, ws2, 'FnB Data');
            
            XLSX.writeFile(wb, 'sales_report.xlsx');
        });
    </script>
<!-- 
<div id="reportrange" class="pull-right" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 10%">
    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
    <span></span> <b class="caret"></b>
</div>

<script type="text/javascript">
$(function() {

    var start = moment().subtract(29, 'days');
    var end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    cb(start, end);
    
});
</script>

<style>
  .select,
  #locale {
    width: 100%;
  }
  .like {
    margin-right: 10px;
  }
</style>

<div id="toolbar">
  <button id="remove" class="btn btn-danger" disabled>
    <i class="fa fa-trash"></i> Delete
  </button>
</div>
<table
  id="table"
  data-toolbar="#toolbar"
  data-search="true"
  data-show-refresh="true"
  data-show-toggle="true"
  data-show-fullscreen="true"
  data-show-columns="true"
  data-show-columns-toggle-all="true"
  data-detail-view="true"
  data-show-export="true"
  data-click-to-select="true"
  data-detail-formatter="detailFormatter"
  data-minimum-count-columns="2"
  data-show-pagination-switch="true"
  data-pagination="true"
  data-id-field="id"
  data-page-list="[10, 25, 50, 100, all]"
  data-show-footer="true"
  data-side-pagination="server"
  data-url="proses/get_data_sales.php"
  data-response-handler="responseHandler">
</table>

<script>
  var $table = $('#table');
  var $remove = $('#remove');
  var selections = [];

  function getIdSelections() {
    return $.map($table.bootstrapTable('getSelections'), function (row) {
      return row.id;
    });
  }

  function responseHandler(res) {
    $.each(res.rows, function (i, row) {
      row.state = $.inArray(row.id, selections) !== -1;
    });
    return res;
  }

  function detailFormatter(index, row) {
    var html = [];
    $.each(row, function (key, value) {
      html.push('<p><b>' + key + ':</b> ' + value + '</p>');
    });
    return html.join('');
  }

  function operateFormatter(value, row, index) {
    return [
      '<a class="like" href="javascript:void(0)" title="Like">',
      '<i class="fa fa-heart"></i>',
      '</a>  ',
      '<a class="remove" href="javascript:void(0)" title="Remove">',
      '<i class="fa fa-trash"></i>',
      '</a>'
    ].join('');
  }

  window.operateEvents = {
    'click .like': function (e, value, row, index) {
      alert('You click like action, row: ' + JSON.stringify(row));
    },
    'click .remove': function (e, value, row, index) {
      $table.bootstrapTable('remove', {
        field: 'id',
        values: [row.id]
      });
    }
  };

  function totalTextFormatter(data) {
    return 'Total';
  }

  function totalNameFormatter(data) {
    return data.length;
  }

  function totalPriceFormatter(data) {
    var field = this.field;
    return 'Rp. ' + data.map(function (row) {
      return +row[field].substring(1);
    }).reduce(function (sum, i) {
      return sum + i;
    }, 0);
  }

  function initTable() {
    $table.bootstrapTable('destroy').bootstrapTable({
      height: 550,
      locale: 'id-ID',
      columns: [
        [
          {
            field: 'state',
            checkbox: true,
            rowspan: 2,
            align: 'center',
            valign: 'middle'
          },
          {
            title: 'Item ID',
            field: 'id',
            rowspan: 2,
            align: 'center',
            valign: 'middle',
            sortable: true,
            footerFormatter: totalTextFormatter
          },
          {
            title: 'Item Detail',
            colspan: 3,
            align: 'center'
          }
        ],
        [
          {
            field: 'name',
            title: 'Item Name',
            sortable: true,
            footerFormatter: totalNameFormatter,
            align: 'center'
          },
          {
            field: 'price',
            title: 'Item Price',
            sortable: true,
            align: 'center',
            footerFormatter: totalPriceFormatter
          },
          {
            field: 'operate',
            title: 'Item Operate',
            align: 'center',
            clickToSelect: false,
            events: window.operateEvents,
            formatter: operateFormatter
          }
        ]
      ]
    //   queryParams: function (params) {
    //     var dateRange = $('#date-range').val().split(' - ');
    //     params.startDate = dateRange[0];
    //     params.endDate = dateRange[1];
    //     return params;
    //   }
    });

    $table.on('check.bs.table uncheck.bs.table ' +
      'check-all.bs.table uncheck-all.bs.table',
    function () {
      $remove.prop('disabled', !$table.bootstrapTable('getSelections').length);
      selections = getIdSelections();
    });

    $table.on('all.bs.table', function (e, name, args) {
      console.log(name, args);
    });

    $remove.click(function () {
      var ids = getIdSelections();
      $table.bootstrapTable('remove', {
        field: 'id',
        values: ids
      });
      $remove.prop('disabled', true);
    });
  }

  $(function() {
    initTable();

  });
</script> -->


</body>
</html>

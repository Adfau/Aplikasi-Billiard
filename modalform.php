<?php
// Check if the constant is defined, if not, redirect or display an error message
if (!defined('INCLUDED')) {
    header("HTTP/1.1 404 Not Found");
    exit; // Stop further execution
}
?>
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

              <div class="d-flex align-items-center">
                <h5 class="modal-title" id="addModalLabel">00</h5>
                <h5 class="modal-title" style="margin-left: 10px;">Sewa Meja</h5>
              </div>
              <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>

            </div>
            <div class="modal-body">

                <form id="rentalForm" action="url.php" method="POST">
                    <!-- Toggle button for switching between mode -->
                    <div class="form-check mb-3" style="text-align: left;">
                        <input class="form-check-input" type="checkbox" id="modeToggle" name="billingMode">
                        <label class="form-check-label" for="modeToggle">
                            Booking Mode
                        </label>
                    </div>
                    
                    <input type="hidden" name="no_meja">
                    <div class="form-group">
                        <label for="namaPenyewa">Nama Tamu:</label>
                        <input type="text" class="form-control focused-input" id="namaPenyewa" name="namaPenyewa" maxlength="50">
                    </div>
                    <div class="form-group detailModeFields">
                        <label for="tglMulai">Tanggal Mulai:</label>
                        <input type="date" class="form-control" id="tglMulai" name="tglMulai" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group detailModeFields">
                        <label for="waktuMulai">Waktu Mulai:</label>
                        <input type="time" step="2" class="form-control" id="waktuMulai" name="waktuMulai">
                    </div>
                    <div class="form-group detailModeFields">
                        <label for="waktuSelesai">Waktu Selesai:</label>
                        <input type="time" step="2" class="form-control" id="waktuSelesai" name="waktuSelesai">
                    </div>
                    <div class="form-group">
                        <label for="sisaWaktu">Durasi:</label>
                        <input type="time" step="2" class="form-control" id="sisaWaktu" name="sisaWaktu">
                    </div>
                    <div class="form-group">
                        <label for="harga">Harga:</label>

                        <div style="margin-bottom: 5px;">
                            <input type="number" class="form-control" style="margin-bottom: 0px;" id="harga" name="harga" min="0" oninput="this.value = Math.abs(this.value)" value="0">
                            <small class="text-muted" style="float: left; font-size: 12px;">(<?php echo number_format(getHarga(),0,",",".") . '/jam, ~' . number_format(floor(getHarga() / 60),0,",",".") . '/menit'; ?>)</small>
                        </div>
                        <br>
                        
                    </div>
                    <button type="submit" class="btn btn-primary" id="addSubmitButton">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var modeToggle = document.getElementById("modeToggle");
        var detailModeFields = document.querySelectorAll(".detailModeFields");
        var prevValues = [];

        modeToggle.addEventListener("change", function() {
            if (modeToggle.checked) {
                detailModeFields.forEach(function(field, index) {
                    field.style.display = "block";
                    var input = field.querySelector("input");
                    if (input) {
                        input.setAttribute("required", "required");
                    }
                });
            } else {
                detailModeFields.forEach(function(field) {
                    field.style.display = "none";
                    var input = field.querySelector("input");
                    if (input) {
                        input.removeAttribute("required");
                    }
                });
            }
        });
    });
</script>

<!-- Modal for editing form -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <h5 class="modal-title" id="editModalLabel">00</h5>
                    <h5 class="modal-title" style="margin-left: 10px;">Edit Meja</h5>
                </div>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <form id="editForm" action="url.php" method="POST">  
                    <input type="hidden" name="no_meja">
                    <div class="form-group">
                        <label for="editNamaPenyewa">Nama Tamu:</label>
                        <input type="text" class="form-control focused-input" id="editNamaPenyewa" name="namaPenyewa" maxlength="50">
                    </div>
                    
                    <div class="form-group">
                        <div>Transfer Meja:</div>
                        <div class="align-items-center" style="display: inline-block;" data-toggle="buttons">

                            <?php 
                                // Include the database connection
                                require_once('controller.php');

                                // Enable MySQLi exceptions
                                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                                try {
                                    // Fetch available tables
                                    $sqlModal = "SELECT no_meja from meja";
                                    $resultModal = $con->query($sqlModal);
                                    
                                    // Check if query was successful
                                    if ($resultModal) {
                                        while ($row = mysqli_fetch_array($resultModal)) {
                                            // Generate unique id for each radio button
                                            $optionId = "option" . $row['no_meja'];
                                            $textMeja = ($row['no_meja'] < 10) ? "0".$row['no_meja'] : $row['no_meja'];
                                            if (!empty($status)) {
                                                if(in_array($row['no_meja'], $status)) {
                                                    $styleButton = "-secondary disable-hover";
                                                    $styleStatus = " disabled";
                                                } else {
                                                    $styleButton = "-primary";
                                                    $styleStatus = "";
                                                }
                                                echo '<div class="btn btn-outline'. $styleButton .'" style="min-width: 50px; margin-right: 5px; margin-bottom: 5px;">';
                                                echo '<input'. $styleStatus .' hidden type="radio" name="transferMeja" id="' . $optionId . '" value="' . $row['no_meja'] . '" autocomplete="off"> ' . $textMeja;
                                                echo '</div>';
                                            }
                                            
                                            
                                        }
                                        $optionId = "option0";
                                        $textMeja = '<i class="fa fa-times" aria-hidden="true"></i>';
                                        $styleButton = "-secondary";
                                        $styleStatus = " checked";
                                        
                                        echo '<div class="btn btn-outline'. $styleButton .'" style="min-width: 50px; margin-right: 5px; margin-bottom: 5px;">';
                                        echo '<input'. $styleStatus .' hidden type="radio" name="transferMeja" id="' . $optionId . '" autocomplete="off"> ' . $textMeja;
                                        echo '</div>';
                                    } else {
                                        echo "Error fetching tables: " . mysqli_error($con);
                                    }
                                } catch (\Exception $e) {
                                    echo '<div class="alert alert-danger" role="alert">Error fetching tables: ' . $e->getMessage() . '</div>';
                                } catch (\Error $e) {
                                    echo '<div class="alert alert-danger" role="alert">Error: ' . $e->getMessage() . '</div>';
                                } catch (mysqli_sql_exception $e) {
                                    echo '<div class="alert alert-danger" role="alert">Error MySQLI: ' . $e->getMessage() . '</div>';
                                }
                            ?>
                        </div>
                    </div>
                    <div style="margin-bottom: 5px;"></div>
                    <button type="submit" class="btn btn-primary" id="submitButton">Edit Data</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal for receipt -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalLabel">Struk Meja</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto; overflow-x: auto;">
                <div class="align-items-center justify-content-center d-flex">
                    <canvas id="pdf-render" style="box-shadow: 0 0 10px #000;"></canvas>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                <form id="printReceipt" action="#" target="_blank" method="POST">
                    <button type="submit" class="btn btn-outline-primary" id="submitButton"><i class="fas fa-print"></i> Cetak</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>

<script>

    // Function to render PDF based on input
    function renderPdfStruk(id) {
        // Send AJAX request to server-side script to generate PDF
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `proses/cetak_struk.php?id=${id}`, true);
        xhr.responseType = 'blob';
        xhr.onload = function(event) {
            if (xhr.status === 200) {
                // Load PDF blob using PDF.js
                const blob = xhr.response;
                const fileReader = new FileReader();
                fileReader.onload = function() {
                    const typedArray = new Uint8Array(this.result);
                    pdfjsLib.getDocument(typedArray).promise.then(function(pdf) {
                        // Render first page of PDF
                        pdf.getPage(1).then(function(page) {
                            const canvas = document.getElementById('pdf-render');
                            const viewport = page.getViewport({ scale: 1.5 });
                            canvas.width = viewport.width;
                            canvas.height = viewport.height;
                            const renderContext = {
                                canvasContext: canvas.getContext('2d'),
                                viewport: viewport
                            };
                            page.render(renderContext);
                        });
                    });
                };
                fileReader.readAsArrayBuffer(blob);
            } else {
                console.error('Error loading PDF:', xhr.statusText);
            }
        };
        xhr.send();
    }
</script>

<!-- Loading Screen -->
<div id="loadingScreen" class="loading-overlay">
    <div class="spinner-border text-light" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>

<!-- Cashier Modal -->
<div class="modal fade" id="cashierModal">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h5 style="position: absolute; left: 10px; top: 6px;"><div id="cashierModalNumber">01</div></h5>
        <div class="modal-box">  Food & Beverages </div>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>


      <!-- Modal Body -->
      <div class="modal-body modal-cashier">
        <!-- Left Side: Part 1 (Cashier Form) and Part 2 (Items Table) -->
        <div class="left-side">
          <!-- Part 1: Cashier Form -->
          <div class="cashier-form" id="cashier-form">
            <!-- <h5>Cashier</h5> -->
            <form id="cashierForm">
              <div class="form-group">
                <label for="productName">Produk</label>
                <input type="text" class="form-control form-control-sm" id="productName" name="productName" maxlength="15">
              </div>
              <div class="form-group" id="formatHarga">
                <label for="price">Harga (Rupiah)</label>
                <input type="text" class="form-control form-control-sm" id="price" name="price" oninput="formatPrice(this)">
              </div>
              <div class="form-group">
                <label for="qty">Qty</label>
                <input type="number" class="form-control form-control-sm" id="qty" name="qty" min="1" oninput="this.value = !!this.value && Math.abs(this.value) >= 0 ? Math.max(1, Math.abs(this.value)) : null">
              </div>
              <div class="form-group">
                <label for="subtotal">Subtotal (Rupiah)</label>
                <input type="text" class="form-control form-control-sm" id="subtotal" name="subtotal" readonly>
              </div>
              <!-- <div class="form-group">
                <label for="netto">Netto (Rupiah)</label>
                <input type="text" class="form-control" id="netto" name="netto" readonly>
              </div> -->
              <div class="d-flex align-items-center justify-content-between">
                <!-- <div>*Nama Produk Tidak Ada</div> -->
              <button type="button" class="btn" id="saveItem">Tambah</button>
                </div>
            </form>
          </div>

          <!-- Part 3 (Product List) -->

          <div class="product-list">
            <div class="d-flex align-items-center justify-content-between">
            <h5>Menu FnB</h5>
            <input type="search" id="searchProduct" class="form-control form-control-sm" placeholder="Cari Menu..." style="max-width: 40%; margin-bottom: 10px;">
            </div>
            <table class="table table-sm table-hover table-bordered" id="productListTable">
              <thead>
                <tr>
                  <th style="width: 70%">Nama</th>
                  <th style="width: 30%">Harga</th>
                </tr>
              </thead>
              <tbody>
                
                <!-- PHP to fetch products from database will go here -->
                <?php
                // Assume $products is fetched from the database
                require_once('controller.php');
                $sql = "SELECT nama_menu, harga_menu FROM fnb_menu ORDER BY nama_menu;";
                $resultCashier = mysqli_query($con, $sql);

                $products = [];
                while ($row = mysqli_fetch_array($resultCashier)) {
                    $products[] = $row;
                }
                foreach ($products as $product) {
                  $harga_product = number_format($product['harga_menu'],0,",",".");;
                  echo "<tr data-name='{$product['nama_menu']}' data-price='{$harga_product}'>";
                  echo '<td style="width: 70%">'.$product['nama_menu'].'</td>';
                  echo '<td style="width: 30%">'.$harga_product.'</td>';
                  echo "</tr>";
                }
                ?>
                
              </tbody>
            </table>
            
          </div>

        </div>

        <!-- Right Side: Part 2: Table of Items -->
        <div class="right-side">
          <div class="items-table">
            <div class="d-flex align-items-center justify-content-between">
              <h5>Items</h5>
              <div class="select-list">0 Selected</div>
            </div>
            <div id="items-cage">
                <table class="table prevent-select" id="itemsTable">
                <div class="items-table-border"></div>
                <thead>
                    <tr>
                    <th>Produk</th>
                    <th style="width: 20%;">Harga</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 20%;">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="scroll-drag">
                </tbody>
                </table>
            </div>
            
            <h5 class="total">Total: <span id="totalAmount">0</span></h5>
            <div style="float: right;">
              <button type="button" class="btn btn-danger" id="deleteItem">Hapus</button>
              <button type="button" class="btn btn-success" id="saveCashierItems" data-billing-id="1">Simpan</button>
            </div>
          </div>
        </div>
        
      </div>

      <!--
      <div class="modal-footer">
      </div> -->

    </div>
  </div>
</div>

<script src="script/fungsi_kasir.js"></script>

  <!-- Checkout Modal -->
  <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
          <h5 style="position: absolute; left: 10px; top: 6px;"><div id="checkoutModalNumber">01</div></h5>
          <div class="modal-box">  Checkout </div>
          <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <form id="checkoutForm" action="url.php" method="POST">
        <!-- Modal Body -->
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <!-- FnB Detail -->
              <div class="col-md-4 fnb-table" style="width: 35%;">
                <h6>Daftar FnB</h6>
                <table class="table" id="fnbCheckout">
                  <thead>
                    <tr>
                      <th style="width: 35%;">Nama FnB</th>
                      <th style="width: 25%;">Harga</th>
                      <th style="width: 15%;">Qty</th>
                      <th style="width: 25%;">Total</th>
                    </tr>
                  </thead>
                  <tbody id="fnb-details">
                    <!-- Add rows dynamically -->
                  </tbody>
                </table>
                <h5 class="total">Total: <span id="totalFnbCheckout">0</span></h5>
              </div>
              <!-- Billing Detail -->
              <div class="col-md-4" style="width: 30%; margin-right: 7px;">
                <h6>Billing Detail</h6>
                <div class="card">
                  <div class="card-body billing-details">
                    <p class="span-right">Tanggal: <span id="tanggal"></span></p>
                    <p class="span-right">No. Nota: <span id="no_faktur"></span></p>
                    <p class="span-right">Paket: <span id="paket"></span></p>
                    <p class="span-right">Meja: <span id="table"></span></p>
                    <p class="span-right">Mulai: <span id="mulai"></span></p>
                    <p class="span-right">Selesai: <span id="selesai"></span></p>
                    <p class="span-right">Durasi: <span id="durasi"></span></p>
                    <p class="span-right">Harga: <span id="harga_table"></span></p>
                    <p class="span-right">Total (Rp): <span id="subtotal_table"></span></p>
                  </div>
                </div>
              </div>
              <!-- Grand Total & Payment -->
              <div class="col-md-4" style="width: 35%;">
                <div style="margin-bottom: 20px;">

                  <div class="form-group">
                    <label for="nama_tamu">Nama Tamu</label>
                    <input type="text" class="form-control" id="nama_tamu" name="c_NamaTamu">
                  </div>
                  <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea style="resize: none;" class="form-control" id="keterangan" rows="2" maxlength="255" name="c_Keterangan">-</textarea>
                  </div>
                </div>

                <div class="cashier-form" id="changeForm">
                  <div class="form-group">
                    <label for="grand_total">Grand total (Rp)</label>
                    <input type="text" class="form-control" id="grand_total" readonly disabled>
                  </div>
                  <div class="form-group">
                    <label for="uang_diterima">Uang diterima (Rp)</label>
                    <input type="text" class="form-control" id="uang_diterima">
                  </div>
                  <div class="form-group">
                    <label for="uang_kembalian">Uang kembalian (Rp)</label>
                    <input type="text" class="form-control" id="uang_kembalian" readonly disabled>
                  </div>
                </div>
                
              </div>
            </div>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
            <!-- <button type="button" class="btn btn-outline-primary"><i class="fa fa-print" aria-hidden="true"></i> Cetak</button> -->
            <input type="hidden" name="id_billing">
            <button type="submit" class="btn btn-secondary disable-hover"><i class="fa fa-check" aria-hidden="true"></i> Deal</button>
            
        </div>

        </form>
      </div>
    </div>
  </div>

  <script src="script/fungsi_checkout.js"></script>
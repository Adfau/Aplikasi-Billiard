<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] != "ADMIN") {
  header( "Location: signin.php" );
  exit();
}

require_once('controller.php');
define('INCLUDED', true);
define('PAGE_ACCOUNT', true);

// KEY! PENTING!!! JANGAN DIUBAH
$key = "4FE961AD538FE21CC27F519235834B12";

// Decryption function
function decrypt($data, $key) {
  $cipher = "aes-256-cbc";
  $data = base64_decode($data);
  $ivlen = openssl_cipher_iv_length($cipher);
  $iv = substr($data, 0, $ivlen);
  $data = substr($data, $ivlen);
  return openssl_decrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
}

$sql = "SELECT * FROM user WHERE level = 'USER'";

if (isset($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $sql = "SELECT * FROM user WHERE level = 'USER' AND `nama_user` LIKE ?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = mysqli_query($con, $sql);
}

$no = 0;

?>


<!DOCTYPE html>
<html lang="en">
<?php include("head.php"); ?>
<style>
    .card {
      margin-bottom: 20px;
    }
    .card-footer .btn {
      margin-right: 10px;
    }
</style>
<body>
    <div class="container-fluid">
        <div class="row no-padding">
            <?php include("header.php"); ?>
            <div class="col no-padding d-flex">
                <?php include("sidebar-admin.php"); ?>
                <div class="container main-content">
                  <div class="row justify-content-center">
                      <!-- Main Content -->
                      <div class="container col-sm-6" style="min-width: 500px;">
                        <h1 class="mt-4 mb-4">Akun Karyawan</h1>

                        <?php  while ($row = mysqli_fetch_array($result)) : ?>
                        <?php
                          $no++;
                          $id = $row['id_user'];
                          $nama = $row['nama_user'];
                          $username = $row['username'];
                          $pin = decrypt($row['dgt000x2_pin'], $key);
                          $note = $row['catatan'];
                        ?>
                        <!-- Profile Card -->
                        <div class="card">

                          <div class="card-body d-flex justify-content-start align-items-center">
                              <div class="profile-lock" style="margin-bottom: 0px; max-width: 75px; margin-right: 40px;">
                                  <img src="https://via.placeholder.com/75" alt="Profile <?php echo $id ?>" style="width: 75px; height: 75px; margin-bottom: 0px; max-width: 75px;">
                                  <p><?php echo $nama ?></p>
                              </div>
                              <div class="align-items-center" style="min-width: 75%; max-width: 75%;">
                                <span>
                                  <label for="username">Username</label>
                                  <input type="text" class="form-control mb-3" id="username" placeholder="Username" value="<?php echo $username ?>" disabled>
                                  <label for="password">PIN</label>
                                  <div class="input-group mb-3">
                                    <input type="password" class="form-control" id="password<?php echo $no; ?>" placeholder="PIN" aria-label="Password" aria-describedby="togglePassword<?php echo $no ?>" disabled value="<?php echo $pin ?>">
                                    <div class="input-group-append">
                                      <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password<?php echo $no; ?>"><i class="fa fa-eye" aria-hidden="true"></i></button>
                                    </div>
                                  </div>
                                  <p class="card-text">Note: <?php echo $note ?></p>
                                </span>
                              </div>
                          </div>
                          <div class="card-footer d-flex justify-content-start">
                              <a href="#" class="btn btn-outline-primary mr-2 editNewButton" id="editNewButton" data-toggle="modal" data-target="#editNewModal" data-user-id="<?php echo $id ?>">Edit</a>
                              <a href="#" class="btn btn-outline-danger" onclick="confirmDelete(<?php echo $id ?>, '<?php echo $nama ?>')">Delete</a>
                          </div>

                        </div>
                        <?php endwhile; ?> 
                          
                        <!-- Add New Card Button -->
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addNewModal" style="margin-bottom: 20px;">
                          Tambah Akun Baru
                        </button>
                        
                        <!-- Modal Tambah -->
                        <div class="modal fade" id="addNewModal" tabindex="-1" aria-labelledby="addNewModalLabel" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="addNewModalLabel">Tambah Akun</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                              <form id="addUserForm" action="proses/tambah_karyawan.php" method="post">
                                <div class="form-group">
                                    <label for="name">Nama</label>
                                    <input type="text" class="form-control" name="nama" id="username" placeholder="Masukkan Nama">
                                  </div>
                                  <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" name="username" id="username" placeholder="Masukkan Username">
                                  </div>
                                  <div class="form-group">
                                    <label for="password">PIN</label>
                                    <input type="password" class="form-control" name="password" id="password" maxlength="4" placeholder="Masukkan Password">
                                  </div>
                                  <div class="form-group">
                                    <label for="note">Note</label>
                                    <textarea class="form-control" id="note" name="note" rows="3" placeholder="Catatan"></textarea>
                                  </div>
                                </form>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary"form="addUserForm" >Save</button>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="editNewModal" tabindex="-1" aria-labelledby="addNewModalLabel" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="editNewModalLabel">Edit Akun</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>

                              <form id="editNewForm" action="url.php" method="POST">  

                              <div class="modal-body">
                                
                                  <input type="hidden" name="id_user" id="id_user">
                                <div class="form-group">
                                    <label for="name">Nama</label>
                                    <input type="text" class="form-control" name="nama" id="e_nama" placeholder="Masukkan Nama">
                                  </div>
                                  <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" name="username" id="e_username" placeholder="Masukkan Username">
                                  </div>
                                  <div class="form-group">
                                    <label for="password">PIN</label>
                                    <input type="password" class="form-control" name="password" id="e_password" maxlength="4" placeholder="Masukkan Password">
                                  </div>
                                  <div class="form-group">
                                    <label for="note">Note</label>
                                    <textarea class="form-control" name="note" id="e_note" rows="3" placeholder="Catatan"></textarea>
                                  </div>
                                
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                              </div>
                              </form>
                            </div>
                          </div>
                        </div>

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

    <script>
      $(document).ready(function(){
          $('.toggle-password').click(function(){
              var target = $(this).data('target');
              var passwordField = $(target);
              var passwordFieldType = passwordField.attr('type');
              if(passwordFieldType == 'password') {
                  passwordField.attr('type', 'text');
                  $(this).html('<i class="fa fa-eye-slash" aria-hidden="true"></i>');
              } else {
                  passwordField.attr('type', 'password');
                  $(this).html('<i class="fa fa-eye" aria-hidden="true"></i>');
              }
          });

          $(document).on('click', '.editNewButton', function() {
            var userId = $(this).data('user-id');
            var tableId = ''; // Assuming you have tableId defined somewhere

            // Ajax to fetch data
            $.ajax({
                url: 'proses/get_data_user.php',
                type: 'POST',
                data: { id_user: userId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === "success") {
                        var data = response.data;
                        $('#e_nama').val(data.nama_user);
                        $('#e_username').val(data.username);
                        $('#e_password').val(data.password);
                        $('#e_note').val(data.catatan);
                        // Set other fields (nama_user, password, note) similarly
                    } else {
                        console.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("An error occurred: " + error);
                }
            });

            // Update modal label and form action
            $('#editNewModalLabel').text('Edit User');
            $('#editNewForm').attr('action', 'proses/update_karyawan.php?id=' + userId);
            $('#id_user').val(userId);
        });

      });

      function confirmDelete(id, nama) {
        var confirmation = confirm("Hapus Karyawan "+nama+"?");
        
        if (confirmation) {
            window.location.href = "proses/hapus_karyawan.php?id=" + id;
        }
      }
    </script>

</body>
</html>
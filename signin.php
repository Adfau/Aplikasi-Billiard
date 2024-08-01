<?php
session_start();

if (isset($_SESSION['level'])) {
  header( "Location: index.php" );
  exit();
}

require_once('controller.php');
define('INCLUDED', true);

$sql = "SELECT * FROM user WHERE level = 'USER'";
$result = mysqli_query($con, $sql);

$profiles = array();
while ($row = mysqli_fetch_array($result)) {
  $id = $row['id_user'];
  $name = $row['nama_user'];
  $username = $row['username'];
  $image = "https://via.placeholder.com/150";

  $profiles[] = array('id' => $id, 'name' => $name, 'image' => $image, 'username' => $username);
}

?>

<!DOCTYPE html>
<html lang="en">
<?php include("head.php"); ?>

<style>
  body {
    background-image: url("img/login-bg.jpg");
    height: 100vh;

    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
    background-attachment: fixed;
  }
</style>

<body>
  <div class="container">
    <div class="row">
    <div class="col-md-12 text-center" style="margin-top: 50px">
        <h1 class="big-text">(LOGO)<br> Aplikasi Billing Billiard</h1>
      </div>
      <div class="col-md-12 text-center" style="margin-top: 50px">
        <h2>Pilih Profil</h1>
      </div>
    </div>
    <div class="row profiles-container">
      <div class="container">
        <div class="row d-flex justify-content-center align-items-center">
        <?php  foreach ($profiles as $profile) : ?>
          <div class="col-sm-3">
            <div class="profile-lock" onclick="showPinModal(<?php echo $profile['id'] ?>)">
              <img src="<?php echo $profile['image'] ?>" alt="Profile echo <?php echo $profile['id'] ?>">
              <p><?php echo $profile['name'] ?></p>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>

   <!-- Button for admin login -->
   <div class="row admin-button">
    <div class="col justify-content-center align-items-center bottom-button">
      <button type="button" class="btn btn-dark d-flex justify-content-center align-items-center" style="border-radius: 20px;" onclick="loginAsAdmin()"><i class="fas fa-user-circle" style="margin-right: 10px; font-size: 25px;"></i> Masuk Sebagai Admin</button>
    </div>
  </div>


<!-- PIN Modal -->
<div class="modal fade" id="pinModal" tabindex="-1" aria-labelledby="pinModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pinModalLabel">Masukkan PIN Anda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="profile-lock" style="margin-bottom: 0px;">
        <img id="profileImage" src="" alt="">
        <p id="profileName"></p>
      </div>

      <div class="modal-body">
        <div class="pin-container">
          <div class="pin-digit"></div>
          <div class="pin-digit"></div>
          <div class="pin-digit"></div>
          <div class="pin-digit"></div>
        </div>
        
        <div class="pin-keyboard ">
          <?php for ($i = 1; $i <= 9; $i++) {
            echo '<button class="pin-key btn btn-outline-secondary" onclick="enterPin('.$i.')">'.$i.'</button>';
          } ?>
          <button class="pin-key btn btn-outline-danger" onclick="clearPin()"><i class="fas fa-trash" aria-hidden="true"></i></button>
          <button class="pin-key btn btn-outline-secondary" onclick="enterPin(0)">0</button>
          <button type="button" class="pin-key btn btn-outline-secondary" onclick="deleteDigit()"><i class="fas fa-backspace" aria-hidden="true"></i></button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ADMIN MODAL -->
<div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminModalLabel">Masukkan Password Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form action="proses/cek_login.php" method="POST" class="was-validated"> 
              <input type="hidden" id="username" name="username" value="admin" hidden>
              <input type="hidden" id="level" name="level" value="ADMIN" hidden>   
                <div class="form-group" style="margin-bottom: 20px;">
                    <input type="password" class="form-control" id="password" placeholder="Enter password" name="password" required pattern=".{1,}">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Masuk</button>
                </div>     
            </form>
            </div>
        </div>
    </div>
</div>


  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Custom Script -->
  <script>
    // Show Pin Modal
    function showPinModal(profileId) {
      // Set profile id in data attribute of modal
      $('#pinModal').data('profile-id', profileId);
      // Retrieve profile information based on profileId (for example, from an array or an API call)
      var profiles = <?php echo json_encode($profiles); ?>;
      var profile = profiles.find(profile => profile.id === profileId.toString());
      // Check if profile exists
      if (profile) {
        // Update profile image and name in modal header
        $('#profileImage').attr('src', profile.image);
        $('#profileName').text(profile.name);
        // Show the modal
        $('#pinModal').modal('show');
      } else {
        console.error('Profile not found for ID:', profileId);
      }
    }

    function loginAsAdmin() {
        // Show the modal
        $('#adminModal').modal('show');
        $('#password').focus();
    }

    let pin = '';

    // Function to enter PIN digit and Auto Submit
    function enterPin(digit) {
      if (pin.length < 4) {
        pin += digit;
        updatePinDisplay();
        if (pin.length === 4) {
          var profileId = $('#pinModal').data('profile-id');
          console.log("PIN entered for Profile " + profileId + ": " + pin);
          // Retrieve username from the selected profile
          var profiles = <?php echo json_encode($profiles); ?>;
          var profile = profiles.find(profile => profile.id === profileId.toString());
          var username = profile.username; // Assuming there's a 'username' property in your profile object
          // Create a form dynamically
          var form = $('<form>', {
            'method': 'POST',
            'action': 'proses/cek_login.php',
            'style': 'display: none;' // Hide the form
          }).append(
            $('<input>', {
              'type': 'hidden',
              'name': 'password',
              'value': pin
            }),
            $('<input>', {
              'type': 'hidden',
              'name': 'username',
              'value': username
            }),
            $('<input>', {
              'type': 'hidden',
              'name': 'level',
              'value': 'USER'
            })
          );
          // Append the form to the body and submit it
          $('body').append(form);
          form.submit();
        }
      }
    }


    // Function to clear PIN
    function clearPin() {
      pin = '';
      updatePinDisplay();
    }

    // Function to delete last entered digit
    function deleteDigit() {
      if (pin.length > 0) {
        pin = pin.slice(0, -1);
        updatePinDisplay();
      }
    }

    // Function to update PIN display
    function updatePinDisplay() {
      const pinDigits = document.querySelectorAll('.pin-digit');
      pinDigits.forEach((digit, index) => {
        //digit.style.backgroundColor = index < pin.length ? '#000' : '#fff';
        digit.classList.toggle('active', index < pin.length);
      });
    }

    $(document).ready(function() {
      // $('#pinModal').on('hidden.bs.modal', function () {
      //     resumeReloadTimer();
      // });
      $('#adminModal').on('shown.bs.modal', function () {
        $('#password').focus();
      });
    });


  </script>
</body>
</html>
<?php
// Check if the constant is defined, if not, redirect or display an error message
if (!defined('INCLUDED')) {
    header("HTTP/1.1 404 Not Found");
    exit; // Stop further execution
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Billiard</title>
    <link rel="icon" type="image/x-icon" href="img/billiardball.ico">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!--<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.0/font/bootstrap-icons.css" rel="stylesheet">-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
     <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <!-- <script src="https://kit.fontawesome.com/3c9e1f42ba.js" crossorigin="anonymous"></script> -->
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <!--<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!--<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>-->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.0/dist/bootstrap-table.min.css">

    <script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.29.0/tableExport.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.0/dist/bootstrap-table.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.0/dist/bootstrap-table-locale-all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.0/dist/extensions/export/bootstrap-table-export.min.js"></script>

    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/cashier.css">
    <link rel="stylesheet" href="css/checkout.css">
    <script src="script/sound.js"></script>
    <script src="script/main.js"></script>

    <style>
        body {
            /*background-color: #3C3C3C;*/
            overflow-x: hidden;
        }
        .card-table .card {
            margin-bottom: 25px;
            border: 2px solid transparent;
            border-radius: 20px; /* Rounded corners */
            transition: border-color 0.5s ease; /* Smooth transition */
            /* max-width: 200px; */
        }
        .card-table .card-text {
            margin-bottom: 0px;
            overflow: scroll;
            max-height: 100px; /* Adjust this value as needed */
            white-space: nowrap;
        }
        .card-table .card-text::-webkit-scrollbar {
            width: 0.2px;
            height: 1px;
        }
        .card-table .card-text::-webkit-scrollbar-thumb {
            background-color: #888;
        }
        .card-table .card:hover {
            border-color: black;
        }
        .green-shadow {
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
        }
        .red-shadow {
            background-color: lightgrey;
            box-shadow: 0 0 10px rgba(139, 0, 0, 0.5);
        }
        .yellow-shadow {
            background-color: grey;
            box-shadow: 0 0 10px rgba(253, 226, 51, 4.5);
        }
        .orange-shadow {
            background-color: #EBEBEB;
            box-shadow: 0 0 10px rgba(234, 111, 28, 0.5);
        }
        .blue-shadow {
            background-color: #EBEBEB;
            box-shadow: 0 0 10px rgba(0, 0, 255, 0.5);
            /*box-shadow: 0 0 10px #9ACD3280;*/
        }
        .black-shadow {
            background-color: #EBEBEB;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            /*box-shadow: 0 0 10px #9ACD3280;*/
        }
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-title {
            color: green;
            padding: 5px 10px;
            display: inline-block;
        }
        .numbered-box {
            min-width: 60px;
            text-align: center;
            border-radius: 10px;
        }
        .modal-box {
            min-width: 60px;
            max-width: fit-content;
            padding: 5px 20px;
            border-radius: 20px;

            text-align: center;
            background-color: #146569;
            color: white;
            box-shadow: 1px 1px 10px #00000066;
            font-size: 20px;
            font-weight: 400;
            font-style: italic;

            position: absolute;
            top: -10px;
            left: 0;
            right: 0;
            margin-left: auto;
            margin-right: auto;
        }
        .signature-box {
            background-color: white;
            color: #146569;
            border: 1px solid #146569;
            
        }
        .green-box {
            background-color: green;
            color: white;
        }
        .green-text {
            color: green;
        }
        .green-shadow-text {
            text-shadow: 0 0 2px #00ff0080;
            font-size: 25px;
        }
        .red-box {
            background-color: red;
            color: white;
        }
        .red-text {
            color: red;
        }
        .yellow-box {
            background-color: #FDE233;
            color: black;
        }
        .yellow-text {
            color: #FDE233;
            text-shadow: 0.2px 1px #3b444b;
        }
        .orange-box {
            background-color: #EA6F1C;
            color: white;
        }
        .orange-text {
            color: #EA6F1C;
        } 
        .blue-box {
            background-color: blue;
            color: white;
        }
        .blue-text {
            color: blue;
        }
        .black-box {
            background-color: black;
            color: white;
        }
        .black-text {
            color: black;
        }

        .fsbig {
            font-size: 18px;
        }

        #formOverlay {
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        #formPopup {
            background-color: #fefefe;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        #closeButton {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 24px;
        }

        #rentalForm {
            text-align: center;
        }

        #rentalForm label {
            display: block;
        }

        #rentalForm input {
            margin-bottom: 10px;
        }

        .modal input {
            margin-bottom: 10px;
        }

        .detailModeFields {
            display: none;
        }

        /* Style for the custom context menu */
        #customContextMenu {
            width: 150px;
            border: 1px solid #515151;
            border-radius: 10px;
            background-color: #2D2D2D;
            background-color: rgba(45, 45, 45, 0.9);
            padding: 5px;
            box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif;
        }

        /* Style for the buttons in the context menu */
        #customContextMenu button {   
            text-align: left;
            border: none;
            background: none;
            padding: 5px;
            cursor: pointer;
            color: white;
        }

        /* Style for the buttons on hover */
        #customContextMenu button:hover {
            background-color: #616161;
        }

        .prevent-select {
            -moz-user-select: none;
            -webkit-user-select: none; /* Safari */
            -ms-user-select: none; /* IE 10 and IE 11 */
            user-select: none; /* Standard syntax */
        }

        /* Adjust dropdown toggle styles */
        .account-dropdown-toggle {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
        }

        /* Adjust icon size and margin */
        .account-dropdown-toggle i {
            font-size: 24px; /* Adjust icon size as needed */
        }

        /* Adjust text margin */
        .account-dropdown-toggle .ml-2 {
            margin-left: 0.5rem; /* Adjust margin as needed */
        }

        .main-container {
            display: flex;
            flex-direction: column;
            height: 100vh; /* Adjust as needed */
        }

        .no-padding {
            padding-left: 0;
            padding-right: 0;
        }

        p.card-text>span {
            display: inline-block;
            min-width: 70px;
        }

        h6.card-subtitle>span {
            display: inline-block;
            min-width: 80px;
        }

        .circle-btn {
            font-size: 1em;
            padding: 5px 10px; /* Adjust padding to space out the dots */
            border: none;
            background: none;
            cursor: pointer;
            position: relative;
            transition: color 0.3s;
        }
        .circle-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: grey;
            opacity: 0; /* Initially hidden */
            transition: opacity 0.3s; /* Fade-in transition */
        }
        .circle-btn:hover::before {
            opacity: 0.5; /* Fade-in when hovering */
        }

        .active-page {
            background-color: #146569 !important;
            text-shadow: 0 0 5px #00ff0080;
        }

        .bottom-button {
            position: fixed;
            bottom: 20px; /* Adjust this value to change the distance from the bottom */
            left: 50%; /* To center horizontally */
            transform: translateX(-50%);
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Transparent black background */
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999; /* High z-index to cover other elements */
        }
        
        .hidden {
            position: absolute;
            opacity: 0;
            display: none;
        }

        .disable-hover {
            pointer-events: none;
        }

        
    </style>

<style>
    #sidebar {
        height: 100%;
        width: 50px;
        position: fixed;
        z-index: 1;
        top: 0;
        left: 0;
        background-color: #043232;
        overflow-x: hidden;
        transition: 0.3s;
        padding-top: 50px;
      }

      #sidebar a {
        padding: 8px 8px 8px 32px;
        text-decoration: none;
        font-size: 18px;
        color: #818181;
        display: block;
        transition: 0.3s;
      }

      #sidebar a:hover {
        color: #f1f1f1;
      }

      #sidebar .closebtn {
        position: absolute;
        top: 0;
        right: 25px;
        font-size: 36px;
        margin-left: 50px;
      }

      #sidebar-show {
        width: 50px;
        transition: 0.3s;
      }
      
    @media only screen and (max-width: 600px) {
        .green-shadow-text {
            font-size: 16px;
        }
    }
    @media only screen and (max-width: 380px) {
        .green-shadow-text {
            font-size: 12px;
        }
    }

  </style>

<style>
    .profile-card {
      border-radius: 50%;
      border: 5px solid #fff;
      overflow: hidden;
      cursor: pointer;
      margin: 0 auto;
      display: block;
    }
    .profile-img {
      width: 150px;
      height: 150px;
      object-fit: cover;
    }
    .profiles-container {
      text-align: center;
    }
    .big-text {
      font-size: 2rem;
      margin-bottom: 20px;
    }
    /* Custom styling for PIN modal */
/* Custom styling for PIN modal */
/* Custom styling for PIN modal */
.pin-container {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 20px;
}

.pin-digit {
  width: 50px;
  height: 50px;
  border: 1px solid #ccc;
  margin: 0 5px;
  background-color: #fff;
  position: relative;
}

.pin-keyboard {
  width: 200px;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  justify-items: center;
  margin: auto;
}

.pin-key {
  width: 50px;
  height: 50px;
  border: 1px solid #ccc;
  font-size: 20px;
}
.pin-digit::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #000;
  opacity: 0;
}

.pin-digit.active::before {
  opacity: 1;
}

    .profile-lock {
      margin: 20px auto;
      width: 200px;
      text-align: center;
      cursor: pointer;
    }
    .profile-lock img {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      transition: transform 0.3s ease-in-out;
    }
    .profile-lock p {
      margin-top: 5px;
      transition: transform 0.3s ease-in-out;
    }
    .profile-lock:hover img {
      transform: scale(1.1);
    }
    .profile-lock:hover p {
      transform: translateY(30%);
    }
    .admin-button {
      margin-top: 80px;
      text-align: center;
    }
    

  </style>
</head>
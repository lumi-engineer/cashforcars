<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Template</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f4f4f4;
    }
    .container {
      width: 90%;
      margin: 0 auto;
    }
    .card {
      background-color: #fff;
      border-radius: 10px;
      overflow: hidden;
      margin: 20px 0;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .header {
      text-align: center;
      padding: 20px;
      background-color: #D9EB4E;
      color: #fff;
    }
    .header img {
      max-width: 200px !important;
      height: auto;
    }
    .content {
      padding: 20px;
    }
    .footer {
      text-align: center;
      padding: 10px;
      background-color: #f4f4f4;
    }
    .text-center{
      text-align: center;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="card">
    <!-- Header with Logo -->
    <div class="header">
      <img src="https://cashforcarsjunkcarremoval.com/wp-content/uploads/2023/03/CASH-FOR-CARS-JUNK-CAR-REMOVAL-300-%C3%97-250-px-1.png" alt="Logo">
    </div>
    
    <!-- Body Content -->
    <div class="content text-center">
      <div class="form-group text-center">
        <label for="phone">Ta da! We'd love to buy your {{vehicle}} for</label>
        <div id="result">
        <h2 data-id="offer_presentation_price" class="_b22f1561 _f09a3dc7 _86719728" style="color: rgb(24, 114, 237);">${{price}}</h2>
    </div>
  </div>
      Please  <a href="{{login}}" style="text-decoration: none; color: #007bff;">login</a> or <a href="{{register}}" style="text-decoration: none; color: #007bff;">register</a>  to accept our offer
    </div>
    
    <!-- Footer -->
    <div class="footer text-center">
      © 2024 cashforcarsjunkcarremoval.com
    </div>
  </div>
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RentEase</title>
    <link rel="stylesheet" href="frontpage.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
  </head>
  <body>
    <header>
      <div class="logo">RENT <span>EASE</span></div>
      <nav>
        <a href="#">WISHLIST <i class="fa-solid fa-heart"></i></a>
        <a href="../login/login.html">LOGIN <i class="fa-solid fa-user"></i></a>
        <a href="../profiledashbord/addroom.html"
          >ADD PROPERTY <i class="fa-solid fa-plus"></i
        ></a>
        <button class="btn-outline"></button>
        <button class="btn-outline">FIND ME ROOM</button>
        <button class="btn-orange" id="shiftBtn">
          <i class="fa-solid fa-truck"></i> SHIFT
        </button>
      </nav>
    </header>

    <section class="hero">
      <div class="search-bar">
        <input type="text" placeholder="Search By Title" />
        <input type="text" placeholder="Search For Location" />
        <select>
          <option>Property Type</option>
        </select>
        <select>
          <option>Select Budget</option>
        </select>
        <button class="btn-orange">Search</button>
      </div>
    </section>

    <section class="banner">
      <p>
        न त हामी Mero Bazaar हो, न त हामी Dalay Bhai, हो.<br />
        हामी त बस <span>rentease.com</span>.
      </p>
      <div class="post-banner" id="animated-text">
        POST YOUR RENT FOR FREE<br /><small>www.rentease.com</small>
      </div>
    </section>

    <script src="frontpage.js"></script>
  </body>
</html>

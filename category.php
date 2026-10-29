<?php 
session_start();

# If not category ID is set
if (!isset($_GET['id'])) {
	header("Location: index.php");
	exit;
}

# Get category ID from GET request
$id = $_GET['id'];

# Database Connection File
include "db_conn.php";

# Book helper function
include "php/func-book.php";
$books = get_books_by_category($conn, $id);

# author helper function
include "php/func-author.php";
$authors = get_all_author($conn);

# Category helper function
include "php/func-category.php";
$categories = get_all_categories($conn);
$current_category = get_category($conn, $id);

 ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?=$current_category['name']?></title>

    <!-- bootstrap 5 CDN-->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">

    <!-- bootstrap 5 Js bundle CDN-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">

	<style>
		/* Theme toggle button */
		.theme-toggle-btn {
			display: flex; align-items: center; gap: 8px;
			background: var(--toggle-bg, #e2e8f0); border: none; border-radius: 50px;
			padding: 6px 14px 6px 10px; cursor: pointer; font-size: 0.82rem;
			font-weight: 600; color: var(--text-primary, #212529); transition: all 0.3s ease; white-space: nowrap;
		}
		.theme-toggle-btn:hover { transform: scale(1.04); }

		/* Brand Logo */
		.brand-logo {
			display: flex !important; align-items: center;
			gap: 13px; text-decoration: none !important; padding: 4px 0;
		}
		.brand-icon-wrap {
			width: 56px; height: 56px;
			background: linear-gradient(145deg, #0b1a38 0%, #1a3a6b 50%, #1c5fa8 100%);
			border-radius: 16px; display: flex; align-items: center; justify-content: center;
			flex-shrink: 0;
			box-shadow: 0 8px 24px rgba(11,26,56,0.55), 0 2px 6px rgba(28,95,168,0.4), inset 0 1px 0 rgba(255,255,255,0.15);
			transition: transform 0.28s cubic-bezier(.34,1.56,.64,1), box-shadow 0.28s ease;
			position: relative;
		}
		.brand-logo:hover .brand-icon-wrap {
			transform: translateY(-3px) rotate(-3deg) scale(1.07);
		}
		.brand-icon-wrap::after {
			content: ''; position: absolute; inset: 0;
			border-radius: 16px; border: 1.5px solid rgba(255,255,255,0.2); pointer-events: none;
		}
		.brand-icon {
			font-size: 2.1rem; line-height: 1; position: relative; z-index: 1;
			filter: drop-shadow(0 2px 5px rgba(0,0,0,0.55));
			transition: transform 0.28s cubic-bezier(.34,1.56,.64,1);
		}
		.brand-logo:hover .brand-icon { transform: scale(1.12) rotate(6deg); }
		.brand-text { display: flex; flex-direction: column; line-height: 1; gap: 4px; }
		.brand-main {
			font-family: 'Abril Fatface', serif; font-size: 1.55rem; font-weight: 400;
			background: linear-gradient(135deg, #0b1a38 20%, #1565c0 70%, #2196f3 100%);
			-webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
		}
		.brand-sub {
			font-family: 'DM Sans', sans-serif; font-size: 0.58rem; font-weight: 600;
			letter-spacing: 0.25em; text-transform: uppercase; color: #7a8fac;
			-webkit-text-fill-color: #7a8fac; display: flex; align-items: center; gap: 6px;
		}
		.brand-sub::before, .brand-sub::after {
			content: ''; display: inline-block; width: 18px; height: 1px;
		}
		.brand-sub::before { background: linear-gradient(90deg, transparent, #a0b4cc); }
		.brand-sub::after  { background: linear-gradient(90deg, #a0b4cc, transparent); }
	</style>

	<script>
		(function () {
			const saved = localStorage.getItem('theme') || 'light';
			document.documentElement.setAttribute('data-theme', saved);
		})();
	</script>

</head>
<body>
	<div class="container">
		<nav class="navbar navbar-expand-lg navbar-light bg-light">
		  <div class="container-fluid">
		    <a class="navbar-brand brand-logo" href="index.php">
				<span class="brand-icon-wrap">
					<span class="brand-icon">📚</span>
				</span>
				<span class="brand-text">
					<span class="brand-main">Online Book Store</span>
					<span class="brand-sub">Your Digital Library</span>
				</span>
			</a>
		    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		      <span class="navbar-toggler-icon"></span>
		    </button>
		    <div class="collapse navbar-collapse" 
		         id="navbarSupportedContent">
		      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
		        <li class="nav-item">
		          <a class="nav-link active" 
		             aria-current="page" 
		             href="index.php">Store</a>
		        </li>
		        <li class="nav-item">
		          <a class="nav-link" 
		             href="about.php">About</a>
		        </li>
		        <li class="nav-item">
		          <?php if (isset($_SESSION['user_id'])) {?>
		          	<a class="nav-link" 
		             href="admin.php">Admin</a>
		          <?php }else{ ?>
		          <a class="nav-link" 
		             href="login.php">Login</a>
		          <?php } ?>
		        </li>
		      </ul>

			  <!-- Dark / Light Toggle -->
			  <button class="theme-toggle-btn" onclick="toggleTheme()" title="Toggle dark/light mode">
				  <span id="toggleIcon">🌙</span>
				  <span id="themeLabel">Dark Mode</span>
			  </button>

		    </div>
		  </div>
		</nav>
		<h1 class="display-4 p-3 fs-3"> 
			<a href="index.php"
			   class="nd">
				<img src="img/back-arrow.PNG" 
				     width="35">
			</a>
		   <?=$current_category['name']?>
		</h1>
		<div class="d-flex pt-3">
			<?php if ($books == 0){ ?>
				<div class="alert alert-warning 
        	            text-center p-5" 
        	     role="alert">
        	     <img src="img/empty.png" 
        	          width="100">
        	     <br>
			    There is no book in the database
		       </div>
			<?php }else{ ?>
			<div class="pdf-list d-flex flex-wrap">
				<?php foreach ($books as $book) { ?>
				<div class="card m-1">
					<img src="uploads/cover/<?=$book['cover']?>"
					     class="card-img-top">
					<div class="card-body">
						<h5 class="card-title">
							<?=$book['title']?>
						</h5>
						<p class="card-text">
							<i><b>By:
								<?php foreach($authors as $author){ 
									if ($author['id'] == $book['author_id']) {
										echo $author['name'];
										break;
									}
								?>

								<?php } ?>
							<br></b></i>
							<?=$book['description']?>
							<br><i><b>Category:
								<?php foreach($categories as $category){ 
									if ($category['id'] == $book['category_id']) {
										echo $category['name'];
										break;
									}
								?>

								<?php } ?>
							<br></b></i>
						</p>
                       <a href="uploads/files/<?=$book['file']?>"
                          class="btn btn-success">Open</a>

                        <a href="uploads/files/<?=$book['file']?>"
                          class="btn btn-primary"
                          download="<?=$book['title']?>">Download</a>
					</div>
				</div>
				<?php } ?>
			</div>
		<?php } ?>

		<div class="category">
			<!-- List of categories -->
			<div class="list-group">
				<?php if ($categories == 0){
					// do nothing
				}else{ ?>
				<a href="#"
				   class="list-group-item list-group-item-action active">Category</a>
				   <?php foreach ($categories as $category ) {?>
				  
				   <a href="category.php?id=<?=$category['id']?>"
				      class="list-group-item list-group-item-action">
				      <?=$category['name']?></a>
				<?php } } ?>
			</div>

			<!-- List of authors -->
			<div class="list-group mt-5">
				<?php if ($authors == 0){
					// do nothing
				}else{ ?>
				<a href="#"
				   class="list-group-item list-group-item-action active">Author</a>
				   <?php foreach ($authors as $author ) {?>
				  
				   <a href="author.php?id=<?=$author['id']?>"
				      class="list-group-item list-group-item-action">
				      <?=$author['name']?></a>
				<?php } } ?>
			</div>
		</div>
		</div>
	</div>
	<script>
		function applyTheme(t) {
			document.documentElement.setAttribute('data-theme', t);
			localStorage.setItem('theme', t);
			var icon  = document.getElementById('toggleIcon');
			var label = document.getElementById('themeLabel');
			if (t === 'dark') {
				icon.textContent  = '☀️';
				label.textContent = 'Light Mode';
			} else {
				icon.textContent  = '🌙';
				label.textContent = 'Dark Mode';
			}
		}
		function toggleTheme() {
			applyTheme(
				document.documentElement.getAttribute('data-theme') === 'dark'
					? 'light' : 'dark'
			);
		}

		// Init from localStorage
		applyTheme(localStorage.getItem('theme') || 'light');
	</script>

</body>
</html>
<!DOCTYPE html>
<?php
	$title = "Вход в систему";

	// цвет фона страницы
	if (isset($_GET["background-color"])) {
		$background_color = $_GET["background-color"];
	} else {
		$background_color = 'blue';
	}
	
	// цвет текста на странице
	if (isset($_GET["color"])) {
		$color = $_GET["color"];
	} else {
		$color = "black";
	}
	// шрифт на странице
	if (isset($_GET["font"])) {
		$font = $_GET["font"];
	} else {
		$font = 'courrier';
	}
	// тип отрисовки - canvas или svg
	if (isset($_GET["type"])) {
		$type = $_GET["type"];
	} else {
		$type = 'none';
	}
	// тип отрисовки - текст или графические примитивы
	if (isset($_GET['method'])) {
		$method = $_GET["method"];
	} else {
		$method = 'none';
	}

	if ($method == "graphic") {
		$text = "графическими примитивами";
		$is_graphic = true;
		$is_text = false;
	} else if ($method == "text") {
		$text = "текстом";
		$is_graphic = false;
		$is_text = true;
	} else if ($method == "graphic,text" || $method == "text,graphic") {
		$text = "графическими примитивами и текстом";
		$is_graphic = true;
		$is_text = true;
	}

	include 'connect.php';

	$result = pg_query($conn, "SELECT * FROM categories");
  if (!$result) {
    echo "ошибка\n";
    exit;
  }

  while ($row = pg_fetch_row($result)) {
    echo "id: $row[0] category_name: $row[1]";
    echo "<br />\n";
  }
?>

<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?= $title?></title>
		<style type="text/css" media="all">
			@import url("styles.css");
		</style>
		<style>
			body {
				background-color: <?= $background_color?>;
				font-family: <?= $font?>;
			}
			.container p, .container span {
				color: <?= $color?>;	
			}
			.graph {
				<?php
					if ($type != "svg" && $type != "canvas" && $type != "canvas,svg" && $type != "svg,canvas") {
				?>
				display: none;
				<?php
					}
				?>
			}
			.graph .canvas {
				<?php
					if ($type == "svg") {
				?>
				display: none;
				<?php
					}
				?>
			}
			.graph .svg {
				<?php
					if ($type == "canvas") {
				?>
				display: none;
				<?php
					}
				?>
			}
			line {
        stroke: rgb(73, 4, 129);
      }
      path {
        stroke: rgb(73, 4, 129);
      }
		</style>
	</head>
	<body>
		<div class="container">
			<h1><?= $title?></h1>
			<div class="layer">
				<p>Текст</p>
			</div>

			<div class="graph">
				<div class="canvas">
					<div class="layer">
						<p>Отрисовка Canvas <?= $text?></p>
						<canvas height="150" width="500" id="pic1">Update browser</canvas>
						<script>
							const pic1 = document.getElementById("pic1")
							const picture = pic1.getContext("2d")

							<?php
								if ($is_graphic) {
							?>

							const canvasDrawB = (picture, x) => {
								picture.moveTo(x, 1)
								picture.lineTo(x, 20)
								picture.moveTo(x, 10)
								picture.arc(x, 5, 5, 1.57, 4.71, true)
								picture.moveTo(x, 20)
								picture.arc(x, 15, 5, 1.57, 4.71, true)
							}
							const canvasDrawDCyr = (picture, x) => {
								picture.moveTo(x, 20)
								const h1 = 15
								picture.lineTo(x, h1)
								const dx1 = 10
								picture.lineTo(x + dx1, h1)
								picture.lineTo(x + dx1, 20)
								const dx2 = 2.75
								picture.moveTo(x + dx2, h1)
								picture.lineTo(x + dx2, 1)
								const x1 = x + dx1 - dx2
								picture.lineTo(x1, 1)
								picture.lineTo(x1, h1)
							}

							// графическими примитивами, используя Canvas
							picture.beginPath()
							let x = 1
							//В
							canvasDrawB(picture, x)
							//Д
							x += 14
							canvasDrawDCyr(picture, x)
							//В
							x += 19
							canvasDrawB(picture, x)
							picture.stroke()
							picture.strokeStyle = "black"

							<?php
								}
								if ($is_text) {
							?>

							// отрисовка текста, используя Canvas
							function drawStroked(text, x, y) {
								picture.font = "50px Sana-serif"
								picture.strokeStyle = "black"
								picture.linewidth = 4
								picture.strokeText(text, x, y)
								picture.fillStyle = "black"
								picture.fillText(text, x, y)
							}
							drawStroked("ВДВ", 1, 100)

							<?php
								}
							?>
						</script>
					</div>
				</div>
				<div class="svg">
					<div class="layer">
						<p>Отрисовка SVG <?= $text?></p>
						<svg circle='width="200" height="200"'>
							<?php
								if ($is_graphic) {
							?>
							<!-- графическими примитивами, используя SVG, -->
							<line x1="1" y1="100" x2="1" y2="120" />
							<path
								d="M 1,100 A5,7 90 0, 1 1,110
											M 1,110 A5,7 90 0, 1 1,120"
								fill="none"
							></path>

							<line x1="21" y1="120" x2="21" y2="115" />
							<line x1="31" y1="120" x2="31" y2="115" />
							<line x1="21" y1="115" x2="31" y2="115" />
							<line x1="23.5" y1="115" x2="23.5" y2="100" />
							<line x1="28.5" y1="115" x2="28.5" y2="100" />
							<line x1="23.5" y1="100" x2="28.5" y2="100" />

							<line x1="46" y1="100" x2="46" y2="120" />
							<path
								d="M 46,100 A5,7 90 0, 1 46,110
											M 46,110 A5,7 90 0, 1 46,120"
								fill="none"
							></path>

							<?php
								}
								if ($is_text) {
							?>
							<!-- отрисовкой текста, используя SVG. -->
							<text x="1" y="35" class="small">ВДВ</text>
							<?php
								}
							?>
						</svg>
					</div>
				</div>
			</div>

			<div class="form">
				<div class="layer">
					<form action="user_info.php" method="post">
						<div class="form-group">
							<label for="first_name">Ваше имя:</label>
							<input type="text" name="first_name" placeholder="Введите имя">
						</div>

						<div class="form-group">
							<label for="last_name"><span style="color:rgba(100,100,100,0.5);">Артем</span> Ваша фамилия:</label>
							<input type="text" name="last_name" placeholder="Введите фамилию">
						</div>

						<div class="form-group">
							<label for="user_name">Ваш логин:</label>
							<input type="text" name="user_name" placeholder="Введите логин">
						</div>
						
						<div class="form-group">
							<label for="user_password">Ваш пароль:</label>
							<input type="password" name="user_password" placeholder="Введите пароль">
						</div>

						<div class="form-group">
							<label for="phone">Ваш телефон:</label>
							<input type="tel" name="phone" placeholder="Введите телефон">
						</div>

						<div class="form-group">
							<label for="email">Ваш Email:</label>
							<input type="email" name="email" placeholder="Введите Email">
						</div>

						<button class="btn" type="submit">Войти</button>
					</form>
				</div>
			</div>
		</div>
	</body>
</html>

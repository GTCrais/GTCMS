<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

		<style>
			body {
				margin: 0;
			}

			.outer-container {
				width: 640px;
				max-width: 80%;
				margin: 0 auto;
				padding-top: 20px;
			}

			.container {
				font-family: Tahoma, Verdana, sans-serif;
				color: #686868;
				border-collapse: collapse;
			}

			p {
				word-wrap: break-word;
			}

			.logo-container {
				margin-bottom: 30px;
				max-width: 100%;
			}

			.logo-link {
				display: block;
			}

			.logo-link img {
				width: 100%;
				max-width: 351px;
			}

			.content-container {
				padding: 0 15px 0 15px;
			}

			h2 {
				margin: 0 0 20px 0;
				font-weight: normal;
			}

			a {
				color: #db4343;
				text-decoration: none;
			}

			a:hover {
				text-decoration: underline;
			}

			a.button {
				color: #fff;
				background: #db4343;
				border-radius: 4px;
				padding: 12px 22px 12px 22px;
				font-size: 14px;
				display: inline-block;
			}

			a.button:hover {
				color: #fff;
				text-decoration: none;
				background: #c34444;
			}

			.with-bm {
				margin-bottom: 15px;
			}
		</style>
	</head>

	<body>
		<div class="outer-container">
			<table class="container">
				<tbody>
					<tr>
						<td>
							<div class="logo-container">
								<a class="logo-link" href="/">
									<img src="{{asset("img/" . config('gtcms.emailLogo'))}}" alt="{{config('gtcms.siteName')}}" />
								</a>
							</div>

							<div class="content-container">
								@yield('content')
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</body>
</html>
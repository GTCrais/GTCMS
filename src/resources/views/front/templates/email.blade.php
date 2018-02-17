<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

		<link href="https://fonts.googleapis.com/css?family=Raleway:400,600&amp;subset=latin-ext" rel="stylesheet">

		<style>
			body {
				margin: 0;
				background: #f7f7f7;
			}

			.outer-container {
				width: 640px;
				max-width: 80%;
				margin: 0 auto;
				padding: 35px 30px 30px 30px;
				background: #ffffff;
			}

			.container {
				font-family: 'Raleway', Tahoma, Verdana, sans-serif;
				color: #686868;
				border-collapse: collapse;
				width: 100%;
				line-height: 24px;
			}

			p {
				word-wrap: break-word;
			}

			.logo-container {
				margin: 0 0 30px;
				max-width: 100%;
			}

			.logo-link {
				display: block;
			}

			.logo {
				display: block;
				margin: 0 auto;
				width: 200px !important; /* Increase if needed */
				max-width: 100% !important;
			}

			@media screen and (max-width: 400px) {
				.logo-container {
					width: 150px; /* MAX: 250px */
				}

				.logo {
					width: 150px !important; /* MAX: 250px */
				}
			}

			.content-container {
				padding: 0;
			}

			h2 {
				margin: 0 0 20px 0;
				font-weight: normal;
			}

			a {
				color: #0088cc;
				text-decoration: none;
			}

			a:hover {
				text-decoration: underline;
			}

			.break-all {
				overflow-wrap: break-word;
				word-wrap: break-word;
				-ms-word-break: break-all;
				/* This is the dangerous one in WebKit, as it breaks things wherever */
				word-break: break-all;
				/* Instead use this non-standard one: */
				word-break: break-word;
				-ms-hyphens: auto;
				-moz-hyphens: auto;
				-webkit-hyphens: auto;
				hyphens: auto;
			}

			a.button {
				color: #fff;
				background: #0088cc;
				border-radius: 4px;
				padding: 12px 22px 12px 22px;
				font-size: 14px;
				display: inline-block;
			}

			a.button:hover {
				color: #fff;
				text-decoration: none;
				background: #005580;
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
								<a class="logo-link" href="{{ route('home') }}">
									@if (app()->environment() != 'localdev')
										<img class="logo" width="150" src="{{$message->embed(asset("img/" . config('gtcms.emailLogo')))}}" alt="{{config('gtcms.siteName')}}" />
									@else
										<img class="logo" width="150" src="{{asset("img/" . config('gtcms.emailLogo'))}}" alt="{{config('gtcms.siteName')}}" />
									@endif
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
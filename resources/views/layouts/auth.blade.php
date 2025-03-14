<!doctype html>
<html lang="en">
  <head>
    <title>@yield('title', 'Login | The Goat Academy')</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta
      name="description"
      content="The Goat Academy is a modern platform providing top-notch educational services."
    />

    {{-- favicon --}}
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon" />

    <meta
      name="keywords"
      content="The Goat Academy, e-learning, courses, online education"
    />
    <meta name="author" content="The Goat Academy" />

    <!-- [Favicon] icon -->

    <!-- [Font] Family -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/inter/inter.css') }}" id="main-font-link" />
    <!-- [phosphor Icons] -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/phosphor/duotone/style.css') }}" />
    <!-- [Tabler Icons] -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}" />
    <!-- [Feather Icons] -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}" />
    <!-- [Font Awesome Icons] -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}" />
    <!-- [Material Icons] -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}" />

    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link" />
    <script src="{{ asset('assets/js/tech-stack.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/style-preset.css') }}" />

    <!-- Inline CSS to move sidecontent to the top on small screens -->
    <style>
      @media (max-width: 768px) {
        .auth-wrapper {
          display: flex;
          flex-direction: column-reverse; /* Place sidecontent on top */
        }
        .auth-sidecontent {
          order: -1; /* Ensure it appears before auth-form */
          width: 100%;
        }
        .auth-form {
          width: 100%;
        }
      }
    </style>
  </head>
  <body
    data-pc-preset="preset-3"        {{-- <--- Using preset-3 for a purple-like color scheme --}}
    data-pc-sidebar-caption="true"
    data-pc-layout="vertical"
    data-pc-direction="ltr"
    data-pc-theme_contrast=""
    data-pc-theme="light"
  >
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
      <div class="loader-track">
        <div class="loader-fill"></div>
      </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <div class="auth-main">
      <div class="auth-wrapper v3">
        <div class="auth-form">
          {{-- Top Header with Logo --}}
          <div class="auth-header row">
            <div class="col my-1" 
              style="display: flex; justify-content: center; align-items: center;"
            >
              {{-- Replace with your GOAT Academy Logo if you like --}}
              <a href="#">
                <img src="{{ asset('images/logo.svg') }}" alt="The Goat Academy Logo" width="300" />
              </a>
            </div>
            <div class="col-auto my-1">
              {{-- check if auth add logout --}}
              @if (auth()->check())
                <a href="{{ route('logout') }}" class="">Logout <i class="fa fa-sign-out-alt"></i></a>
              @endif
            </div>
          </div>

          {{-- Card Container --}}
          <div class="card my-5">
            <div class="card-body">
              {{-- 
                This is where your dynamic tab content or login/register forms go.
                We replace the entire content block with @yield('content').
              --}}
              @yield('content')
            </div>
          </div>

          {{-- Footer text --}}
          <div class="auth-footer">
            <p class="m-0">© 2024 The Goat Academy - All Rights Reserved</p>
          </div>
        </div>

        {{-- Side Content (Carousel / Testimonials) --}}
        <div class="auth-sidecontent">
          <div class="p-3 px-lg-5 text-center">
            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
              <div class="carousel-inner">
                {{-- Example Slide 1 --}}
                <div class="carousel-item active">
                  <p class="text-white">
                    At The Goat Academy, every challenge is a stepping stone to greatness. Embrace each obstacle with determination and creativity, and know that every effort you put in today builds a stronger tomorrow for all of us.
                  </p>
                </div>

                <div class="carousel-item ">
                  <p class="text-white">
                    "Success is a journey, not a destination. Your relentless pursuit of excellence, passion for learning, and willingness to innovate transform challenges into opportunities, paving the way for continuous growth and shared triumphs."
                  </p>
                </div>

                <div class="carousel-item ">
                  <p class="text-white">
                    "Together, we create an environment where every idea is valued and every setback is a lesson learned. By uniting our talents and determination, we not only overcome obstacles but also drive the future of our academy toward unprecedented success."
                  </p>
                </div>
              </div>
              <div class="carousel-indicators position-relative mt-3">
                <button
                  type="button"
                  data-bs-target="#carouselExampleIndicators"
                  data-bs-slide-to="0"
                  class="active"
                  aria-current="true"
                  aria-label="Slide 1"
                ></button>
                <button
                  type="button"
                  data-bs-target="#carouselExampleIndicators"
                  data-bs-slide-to="1"
                  aria-label="Slide 2"
                ></button>
                {{-- Add more indicators as needed --}}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- [ Main Content ] end -->

    <!-- [Page Specific JS] start -->
    <!-- Example: You can push scripts from child views using @push('scripts') -->
    @stack('scripts')
    <!-- [Page Specific JS] end -->

    <!-- Required Js -->
    <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/fonts/custom-font.js') }}"></script>
    <script src="{{ asset('assets/js/pcoded.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>

    {{-- Example: setting default layout behaviors --}}
    <script> layout_change('light'); </script>
    <script> change_box_container('false'); </script>
    <script> layout_caption_change('true'); </script>
    <script> layout_rtl_change('false'); </script>
    <script> preset_change('preset-3'); </script>  {{-- <--- Purple preset --}}
    <script> main_layout_change('vertical'); </script>


    <script>
      document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll("form").forEach(function (form) {
          form.addEventListener("submit", function (event) {
            let submitButton = form.querySelector("[type='submit']");
            if (submitButton) {
              submitButton.disabled = true;
              submitButton.innerHTML = "Loading  ...";
            }
          });
        });
      });
    </script>

  </body>
</html>

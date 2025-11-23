{{-- resources/layouts/admin/partials/header.blade.php --}}
<div id="sliderHeader" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="{{ asset('img/header/placeholder_1.jpg') }}" class="d-block w-100" alt="Air Bersih">
    </div>
    <div class="carousel-item">
      <img src="{{ asset('img/header/placeholder_2.jpg') }}" class="d-block w-100" alt="Layanan Air">
    </div>
    <div class="carousel-item">
      <img src="{{ asset('img/header/placeholder_1.jpg') }}" class="d-block w-100" alt="Distribusi Air">
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#sliderHeader" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#sliderHeader" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>


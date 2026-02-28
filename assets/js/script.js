// Плавная прокрутка для якорных ссылок
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    e.preventDefault();
    
    const targetId = this.getAttribute('href');
    if(targetId === '#') return;
    
    const targetElement = document.querySelector(targetId);
    if(targetElement) {
      window.scrollTo({
        top: targetElement.offsetTop - 80,
        behavior: 'smooth'
      });
      
      // Закрываем мобильное меню если открыто
      const navMiddle = document.querySelector('.nav-middle');
      if (navMiddle.classList.contains('active')) {
        navMiddle.classList.remove('active');
      }
    }
  });
});

// Подсветка активного пункта меню при скролле
const sections = document.querySelectorAll('section[id]');
const navLinks = document.querySelectorAll('.nav-links a');

function updateActiveNavLink() {
  let current = '';
  
  sections.forEach(section => {
    const sectionTop = section.offsetTop;
    const sectionHeight = section.clientHeight;
    
    if(window.scrollY >= (sectionTop - 150)) {
      current = section.getAttribute('id');
    }
  });
  
  navLinks.forEach(link => {
    link.classList.remove('active');
    if(link.getAttribute('href') === `#${current}`) {
      link.classList.add('active');
    }
  });
}
document.getElementById('requestForm').addEventListener('submit', async function(e) {
  e.preventDefault();

  // Проверка, что капча заполнена
  const captchaResponse = grecaptcha.getResponse();
  if (!captchaResponse) {
    alert('Пожалуйста, подтвердите, что вы не робот.');
    return;
  }

  // Собираем данные формы
  const formData = new FormData(this);
  formData.append('captcha', captchaResponse);

  try {
    const response = await fetch('send.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success) {
      alert('Заявка успешно отправлена! Менеджер свяжется с вами в течение 15 минут.');
      this.reset();
      grecaptcha.reset(); // сброс капчи
    } else {
      alert('Ошибка: ' + (result.error || 'Не удалось отправить заявку. Попробуйте позже.'));
    }
  } catch (error) {
    alert('Ошибка соединения с сервером. Попробуйте позже.');
    console.error(error);
  }
});
window.addEventListener('scroll', updateActiveNavLink);

// Обработка формы
document.getElementById('requestForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  // Собираем данные формы
  const formData = new FormData(this);
  const data = Object.fromEntries(formData);
  
  // Показываем сообщение об успехе
  alert('ЗАЯВКА ПРИНЯТА • Ваша заявка отправлена. Менеджер свяжется с вами в течение 15 минут.');
  
  // Сбрасываем форму
  this.reset();
});

// Слайдер баннеров
const slider = {
  slides: document.querySelectorAll('.slide'),
  indicators: document.querySelectorAll('.indicator'),
  prevBtn: document.querySelector('.slider-nav.prev'),
  nextBtn: document.querySelector('.slider-nav.next'),
  currentSlide: 0,
  interval: null,
  intervalTime: 5000,
  isAnimating: false,
  
  init() {
    // Запускаем автоматическую смену слайдов
    this.startAutoSlide();
    
    // Добавляем обработчики событий
    this.prevBtn.addEventListener('click', () => this.prevSlide());
    this.nextBtn.addEventListener('click', () => this.nextSlide());
    
    // Добавляем обработчики для индикаторов
    this.indicators.forEach((indicator, index) => {
      indicator.addEventListener('click', () => this.goToSlide(index));
    });
    
    // Останавливаем автоматическую смену при наведении на слайдер
    document.querySelector('.slider-container').addEventListener('mouseenter', () => {
      this.stopAutoSlide();
    });
    
    // Возобновляем автоматическую смену при уходе курсора
    document.querySelector('.slider-container').addEventListener('mouseleave', () => {
      this.startAutoSlide();
    });
    
    // Добавляем обработчик для сенсорных устройств
    this.initTouchEvents();
  },
  
  initTouchEvents() {
    const container = document.querySelector('.slider-container');
    let startX = 0;
    let endX = 0;
    
    container.addEventListener('touchstart', (e) => {
      startX = e.touches[0].clientX;
      this.stopAutoSlide();
    });
    
    container.addEventListener('touchmove', (e) => {
      endX = e.touches[0].clientX;
    });
    
    container.addEventListener('touchend', () => {
      const diff = startX - endX;
      if (Math.abs(diff) > 50) { // Минимальное расстояние свайпа
        if (diff > 0) {
          this.nextSlide();
        } else {
          this.prevSlide();
        }
      }
      this.startAutoSlide();
    });
  },
  
  startAutoSlide() {
    this.stopAutoSlide();
    this.interval = setInterval(() => this.nextSlide(), this.intervalTime);
  },
  
  stopAutoSlide() {
    if (this.interval) {
      clearInterval(this.interval);
      this.interval = null;
    }
  },
  
  nextSlide() {
    if (this.isAnimating) return;
    const nextIndex = (this.currentSlide + 1) % this.slides.length;
    this.goToSlide(nextIndex);
  },
  
  prevSlide() {
    if (this.isAnimating) return;
    const prevIndex = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
    this.goToSlide(prevIndex);
  },
  
  goToSlide(index) {
    if (this.isAnimating || index === this.currentSlide) return;
    
    this.isAnimating = true;
    
    // Скрываем текущий слайд
    this.slides[this.currentSlide].classList.remove('active');
    this.indicators[this.currentSlide].classList.remove('active');
    
    // Показываем новый слайд
    this.currentSlide = index;
    this.slides[this.currentSlide].classList.add('active');
    this.indicators[this.currentSlide].classList.add('active');
    
    // Сбрасываем таймер автоматической смены
    this.startAutoSlide();
    
    setTimeout(() => {
      this.isAnimating = false;
    }, 1200);
  }
};

// Кнопка прокрутки наверх
const scrollToTopBtn = document.getElementById('scrollToTop');

function toggleScrollToTopButton() {
  if (window.scrollY > 300) {
    scrollToTopBtn.classList.add('visible');
  } else {
    scrollToTopBtn.classList.remove('visible');
  }
}

scrollToTopBtn.addEventListener('click', () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
});

window.addEventListener('scroll', toggleScrollToTopButton);

// Мобильное меню
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
const navMiddle = document.querySelector('.nav-middle');

mobileMenuBtn.addEventListener('click', () => {
  navMiddle.classList.toggle('active');
  document.body.style.overflow = navMiddle.classList.contains('active') ? 'hidden' : '';
});

// Закрытие меню при клике на ссылку
document.querySelectorAll('.nav-links a').forEach(link => {
  link.addEventListener('click', () => {
    navMiddle.classList.remove('active');
    document.body.style.overflow = '';
  });
});

// Предотвращение скачков на мобильных устройствах при фокусе
const inputs = document.querySelectorAll('input, textarea, button');
inputs.forEach(input => {
  input.addEventListener('focus', () => {
    document.body.style.overflow = 'hidden';
  });
  
  input.addEventListener('blur', () => {
    document.body.style.overflow = '';
  });
});

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
  slider.init();
  updateActiveNavLink();
  toggleScrollToTopButton();
  
  // Анимация появления элементов при скролле
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animated');
      }
    });
  }, observerOptions);
  
  // Наблюдаем за элементами для анимации
  document.querySelectorAll('.catalog-item, .stat-box, .contact-card').forEach(el => {
    observer.observe(el);
  });
});

// Обработка изменения ориентации
let vh = window.innerHeight * 0.01;
document.documentElement.style.setProperty('--vh', `${vh}px`);

window.addEventListener('resize', () => {
  let vh = window.innerHeight * 0.01;
  document.documentElement.style.setProperty('--vh', `${vh}px`);
});

// Предотвращение масштабирования на мобильных устройствах
let lastTouchEnd = 0;
document.addEventListener('touchend', function(event) {
  const now = (new Date()).getTime();
  if (now - lastTouchEnd <= 300) {
    event.preventDefault();
  }
  lastTouchEnd = now;
}, false);
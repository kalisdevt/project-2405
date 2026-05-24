const menuBtn = document.querySelector('.menu-btn');
const menu = document.querySelector('.menu');

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('supportForm');
    const messages = document.getElementById('formMessages');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            messages.innerHTML = 'Отправка...';

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            const method = localStorage.getItem('isAuthorized') ? 'PUT' : 'POST';

            try {
                const response = await fetch('index.php?route=api/form', {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok) {
                    if (result.credentials) {
                        localStorage.setItem('isAuthorized', 'true');
                        messages.innerHTML = `
                            <p>Успешно! Ваши данные для входа:</p>
                            <p>Логин: <b>${result.credentials.login}</b></p>
                            <p>Пароль: <b>${result.credentials.password}</b></p>
                            <p>Профиль: <a href="${result.credentials.profile_url}" style="color: #ff5d44;">Ссылка</a></p>
                            <p><small>Теперь при повторной отправке данные будут обновляться (метод PUT).</small></p>
                        `;
                    } else {
                        messages.innerHTML = `<p>${result.message}</p>`;
                    }
                } else {
                    messages.innerHTML = `<p style="color: #ff5d44;">Ошибка: ${result.errors ? result.errors.join(', ') : result.message}</p>`;
                }
            } catch (error) {
                messages.innerHTML = `<p style="color: #ff5d44;">Произошла ошибка при отправке запроса.</p>`;
            }
        });
    }
});

menuBtn.addEventListener('click', () => {
  menu.classList.toggle('active');
});

// Плавный скролл
const links = document.querySelectorAll('a[href^="#"]');

links.forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();

    const id = link.getAttribute('href');
    const target = document.querySelector(id);

    target.scrollIntoView({
      behavior: 'smooth'
    });

    menu.classList.remove('active');
  });
});

// Анимация появления
const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('show');
    }
  });
});

const cards = document.querySelectorAll(
  '.feature-card, .support-card, .price-card, .case-card, .team-card'
);

cards.forEach(card => {
  card.classList.add('hidden');
  observer.observe(card);
});

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admintín - @yield('title', 'Selector')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/admintin.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
      body { background: var(--black); }
    </style>
</head>
<body>
    <div class="bg-texture"></div>

    @yield('content')

    <!-- Dynamic Form Container -->
    <div id="dynamicFormContainer"></div>

    <!-- Notificaciones JS -->
    <div id="notify"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        function toast(msg, kind) {
            const n = document.getElementById('notify');
            const t = document.createElement('div');
            t.className = 'toast' + (kind ? ' ' + kind : '');
            t.innerHTML = '<span class="lead">&#9656;</span>' + msg;
            n.appendChild(t);
            setTimeout(() => t.remove(), 3200);
        }

        $(document).ready(function() {
            @if(session('success'))
                toast("{{ session('success') }}");
            @endif
            @if(session('error'))
                toast("{{ session('error') }}", "error");
            @endif
            @if(session('warning'))
                toast("{{ session('warning') }}", "warn");
            @endif
            @if($errors->any())
                toast("Error: revisá los campos del formulario", "error");
            @endif
        });
            
        // Search filter for bands/cards with event delegation
        $(document).on('input', '#catSearch, #yrSearch', function() {
            var q = $(this).val().toLowerCase();
            var items = $('.band, .yr-card');
            var hasVisible = false;
            
            items.each(function() {
                var text = $(this).text().toLowerCase();
                if (text.includes(q)) {
                    $(this).show();
                    hasVisible = true;
                } else {
                    $(this).hide();
                }
            });
            
            if (!hasVisible && q.length > 0) {
                if ($('.empty-hint').length === 0) {
                    $(this).closest('section').append('<div class="empty-hint">Sin coincidencias para "'+q+'"</div>');
                } else {
                    $('.empty-hint').text('Sin coincidencias para "'+q+'"').show();
                }
            } else {
                $('.empty-hint').hide();
            }
        });

        // Dynamic Form Logic (PJAX)
        $(document).on('click', 'a[href*="/create"], a[href*="/edit"]', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    let formScreen = doc.querySelector('#formScreen');
                    
                    if(formScreen) {
                        $('.screen.active').removeClass('active').hide().addClass('was-active');
                        
                        let backBtn = formScreen.querySelector('.back-link');
                        if(backBtn) {
                            backBtn.removeAttribute('href');
                            backBtn.onclick = function(ev) {
                                ev.preventDefault();
                                $('#dynamicFormContainer').empty();
                                $('.was-active').addClass('active').show().removeClass('was-active');
                            };
                        }
                        
                        let cancelBtn = formScreen.querySelector('.btn.ghost');
                        if(cancelBtn && cancelBtn.tagName === 'A') {
                            cancelBtn.removeAttribute('href');
                            cancelBtn.onclick = function(ev) {
                                ev.preventDefault();
                                $('#dynamicFormContainer').empty();
                                $('.was-active').addClass('active').show().removeClass('was-active');
                            };
                        }

                        $('#dynamicFormContainer').empty().append(formScreen);
                        
                        let scripts = formScreen.querySelectorAll('script');
                        scripts.forEach(s => {
                            if (!s.src) {
                                eval(s.innerText);
                            }
                        });
                        
                        window.history.pushState({ dynamicForm: true }, '', url);
                    } else {
                        window.location.href = url;
                    }
                });
        });

        // Intercept Form Submit
        $(document).on('submit', '#dynamicFormContainer form', function(e) {
            e.preventDefault();
            let form = $(this);
            let url = form.attr('action');
            let method = form.attr('method') || 'POST';
            let formData = new FormData(this);
            
            let btn = form.find('button[type="submit"]');
            let originalText = btn.html();
            btn.prop('disabled', true).html('GUARDANDO...');
            
            fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            }).then(async res => {
                if(res.redirected) {
                     let nextHtml = await res.text();
                     let parser = new DOMParser();
                     let doc = parser.parseFromString(nextHtml, 'text/html');
                     
                     let newScreen = doc.querySelector('.screen:not(#formScreen)');
                     if(newScreen) {
                         $('.was-active').replaceWith(newScreen);
                     } else {
                         window.location.href = res.url;
                         return;
                     }
                     $('#dynamicFormContainer').empty();
                     
                     window.history.pushState({}, '', res.url);
                     
                     let docScripts = doc.querySelectorAll('script');
                     docScripts.forEach(s => {
                        if(!s.src && s.innerText.includes('toast(')) {
                            eval(s.innerText);
                        }
                     });
                } else if (res.status === 422) {
                     let data = await res.json();
                     form.find('.text-danger').remove();
                     form.find('input, select, textarea').css('border-color', '');
                     for(let field in data.errors) {
                         let inputName = field.includes('.') ? field.split('.').reduce((a,c,i) => i===0 ? c : a+'['+c+']', '') : field;
                         let input = form.find('[name="'+inputName+'"]');
                         if(input.length === 0) input = form.find('[name="'+field+'"]');
                         
                         input.css('border-color', 'var(--racing)');
                         input.after('<div class="text-danger" style="font-size:12px; margin-top:6px;">'+data.errors[field][0]+'</div>');
                     }
                     toast("Error: revisá los campos del formulario", "error");
                     btn.prop('disabled', false).html(originalText);
                } else {
                     toast("Error en el servidor o redirigido", "error");
                     btn.prop('disabled', false).html(originalText);
                }
            }).catch(err => {
                toast("Error en la conexión", "error");
                btn.prop('disabled', false).html(originalText);
            });
        });

        window.addEventListener('popstate', function(e) {
            if ($('#dynamicFormContainer').children().length > 0) {
                $('#dynamicFormContainer').empty();
                $('.was-active').addClass('active').show().removeClass('was-active');
            } else {
                window.location.reload();
            }
        });
    </script>
</body>
</html>

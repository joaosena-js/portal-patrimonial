/* Comportamentos de interface. Sem JavaScript inline, por causa da Content Security Policy. */
document.addEventListener('DOMContentLoaded', () => {
    // Confirmação antes de ações críticas: <button data-confirmar="Mensagem?">
    document.querySelectorAll('[data-confirmar]').forEach((elemento) => {
        elemento.addEventListener('click', (evento) => {
            if (!window.confirm(elemento.dataset.confirmar)) {
                evento.preventDefault();
            }
        });
    });

    // Formulário de módulo: mostra o campo de texto ou o de endereço conforme o tipo.
    document.querySelectorAll('[data-alternar-tipo]').forEach((select) => {
        const alternar = () => {
            const ehTexto = select.value === 'texto';

            document.querySelectorAll('[data-campo-tipo]').forEach((campo) => {
                const campoDeTexto = campo.dataset.campoTipo === 'texto';
                campo.classList.toggle('d-none', ehTexto !== campoDeTexto);
            });
        };

        select.addEventListener('change', alternar);
        alternar();
    });

    // Evita envio duplicado do formulário.
    document.querySelectorAll('form').forEach((formulario) => {
        formulario.addEventListener('submit', () => {
            const botao = formulario.querySelector('button[type="submit"]');

            if (botao) {
                setTimeout(() => {
                    botao.disabled = true;
                }, 0);
            }
        });
    });
});

// Ao voltar pelo histórico do navegador, reativa botões desativados no envio.
window.addEventListener('pageshow', (evento) => {
    if (evento.persisted) {
        document.querySelectorAll('button[type="submit"]').forEach((botao) => {
            botao.disabled = false;
        });
    }
});

/* ---------- Central de Conhecimento ---------- */
document.addEventListener('DOMContentLoaded', () => {
    // Mostra ou esconde campos conforme a opção escolhida: <select data-controla-campos> e
    // <div data-mostrar-quando="valor1,valor2">.
    document.querySelectorAll('[data-controla-campos]').forEach((select) => {
        const alternar = () => {
            document.querySelectorAll('[data-mostrar-quando]').forEach((campo) => {
                const valores = campo.dataset.mostrarQuando.split(',');
                campo.classList.toggle('d-none', !valores.includes(select.value));
            });
        };

        select.addEventListener('change', alternar);
        alternar();
    });

    // Autocadastro: mostra a prévia do usuário (nome.sobrenome). O servidor define o valor final
    // e acrescenta um número se o usuário já existir.
    const campoNome = document.querySelector('[data-gerar-usuario]');
    const previa = document.querySelector('[data-usuario-preview]');

    if (campoNome && previa) {
        const particulas = ['de', 'da', 'do', 'das', 'dos', 'e'];

        const gerar = (nome) => {
            const palavras = nome
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .split(/\s+/)
                .map((palavra) => palavra.replace(/[^a-z0-9]/g, ''))
                .filter((palavra) => palavra !== '' && !particulas.includes(palavra));

            if (palavras.length === 0) {
                return 'nome.sobrenome';
            }

            return palavras.length === 1 ? palavras[0] : palavras[0] + '.' + palavras[palavras.length - 1];
        };

        const atualizar = () => {
            previa.textContent = gerar(campoNome.value);
        };

        campoNome.addEventListener('input', atualizar);
        atualizar();
    }
});

/* ---------- Pop-up de novidades ---------- */
document.addEventListener('DOMContentLoaded', () => {
    const janela = document.querySelector('[data-modal-novidades]');

    if (janela && window.bootstrap) {
        new window.bootstrap.Modal(janela).show();
    }
});

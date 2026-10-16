# Central de Conhecimento Patrimonial (piloto)

Portal da Coordenadoria de Patrimônio (CPAT/SEAD) para compartilhar conhecimento com os gestores
patrimoniais: vídeos operacionais do AJURI, materiais de apoio, legislação, contatos e trilhas.
Não emite certificados e não substitui as capacitações da ESASP.

## O que o portal oferece

| Recurso | Descrição |
|---------|-----------|
| Autocadastro | O gestor cria a conta e já entra. Usuário `nome.sobrenome` gerado automaticamente; CPF validado e guardado só como hash |
| Vídeos operacionais | Upload (MP4/WebM), link do YouTube ou link institucional; categorias, pesquisa, filtros e ordenação |
| Materiais de apoio | Cartilhas, manuais, checklists, fluxogramas, modelos e formulários em acordeões por assunto |
| Legislação e normativos | Leis, decretos, portarias, instruções normativas, orientações técnicas e normativos internos, com pesquisa e download |
| Perguntas frequentes | Por categoria, com links para vídeos, materiais, legislação e contatos relacionados |
| Contatos | CPAT, DBM e DBI: telefone, e-mail, horário e competências |
| Novidades | Sino no topo e pop-up no acesso ("Visualizar agora" ou "Lembrar depois"), com leitura registrada por usuário |
| Pesquisa global | Campo no topo: trilhas, vídeos, materiais, legislação, perguntas e avisos |
| Painel administrativo | 9 indicadores, acompanhamento por trilha e atividades recentes |
| Trilhas e avisos | Trilhas com módulos e progresso; avisos, com destaque para os urgentes |

Não emite certificados e não substitui as capacitações da ESASP.

## Requisitos

- PHP 8.0 ou superior, com `pdo_mysql` e `mbstring` (já vêm no XAMPP)
- MySQL 5.7+ ou MariaDB 10.3+
- Apache com `mod_rewrite` e `AllowOverride All` (padrão do XAMPP)

## Instalação no XAMPP (Windows)

1. Copie a pasta para `C:\xampp\htdocs\portal-patrimonial` e inicie Apache e MySQL.
2. No phpMyAdmin, se o banco `portal_patrimonial` já existir, apague-o (`DROP DATABASE portal_patrimonial;`).
3. Aba **SQL**: execute `database/portal_patrimonial.sql` e depois `database/dados_teste.sql` (opcional).
4. Copie `.env.example` para `.env` e preencha. Gere a `APP_KEY` no terminal e cole no `.env`:

   ```
   C:\xampp\php\php.exe -r "echo bin2hex(random_bytes(32));"
   ```

   Gere uma vez e não altere depois: ela protege o hash dos CPFs.
5. Crie o primeiro administrador:

   ```
   C:\xampp\php\php.exe database\criar_admin.php
   ```

6. Acesse `http://localhost/portal-patrimonial/`.

## Upload de arquivos e vídeos

Os arquivos ficam em `storage/uploads`, fora da pasta pública, com nome aleatório. Só são entregues
por rotas que exigem login. Formatos: PDF, DOCX e XLSX (até 20 MB) e MP4 ou WebM (até 200 MB).

O PHP também limita o envio. Para vídeos maiores que 40 MB no XAMPP, edite `C:\xampp\php\php.ini`:

```
upload_max_filesize = 200M
post_max_size = 210M
```

Reinicie o Apache depois. Para vídeos longos, prefira cadastrar o link do YouTube ou o link institucional.

## E-mail

Por padrão (`MAIL_DRIVER=log`), os e-mails de aprovação (usados só quando o autocadastro exige aprovação) são gravados em `storage/logs/emails.log`
e não são enviados de verdade. Para envio real, o Departamento de Tecnologia precisa fornecer o SMTP
institucional (ou configurar o `sendmail` do servidor e usar `MAIL_DRIVER=mail`).

## Estrutura

```
app/Core/         Router, Controller, Model, View, Database, Logger, ErrorHandler, Env, Config
app/Controllers/  Controllers (área do gestor); Admin/ concentra a gestão de conteúdos
app/Models/       Acesso ao banco (PDO, prepared statements)
app/Services/     Regras que envolvem mais de um model: ProgressoService, ArmazenamentoArquivos, EnvioArquivo, Mailer
app/Helpers/      Session, Csrf, Auth, Validator, Cpf, Texto, Rotulos e funções de view
app/Views/        Telas, layouts e partials
database/         Esquema, dados de teste e script do primeiro administrador
public/           Único diretório acessível pelo navegador
storage/          Logs e uploads (fora do controle de versão)
```

## Segurança implementada

- Senhas com `password_hash`; bloqueio temporário após 5 falhas; mensagem única de falha de login
- CPF validado e guardado apenas como HMAC (nunca o número); autocadastro com campo-armadilha e limite por IP
- Autocadastro com acesso imediato ou com aprovação do administrador (`CADASTRO_REQUER_APROVACAO`)
- Prepared statements em todas as consultas; token CSRF em todo POST; saída escapada
- Uploads com extensão restrita, conferência da assinatura do arquivo, nome aleatório e armazenamento fora da pasta pública
- Perfil verificado no servidor em toda rota; Content Security Policy; erros somente em log
- Auditoria de ações (logins, downloads, visualização de vídeos, aprovações) na tabela `logs_atividade`

## Antes de colocar em produção

- HTTPS, usuário de banco próprio e `APP_DEBUG=false`
- Definir SMTP, rotina de backup do banco e da pasta `storage/uploads`
- Hospedar Bootstrap e Bootstrap Icons localmente se a rede do órgão restringir acesso externo
- Validar com o Departamento de Tecnologia e a área de proteção de dados o tratamento de CPF e demais dados pessoais

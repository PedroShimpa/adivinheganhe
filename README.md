<h1 align="center">🎯 Adivinhe e Ganhe</h1>

<p align="center">
  <b>Uma alternativa brasileira, justa e divertida às apostas convencionais.</b><br>
  <i>Totalmente transparente, em breve 100% gratuito.</i>
</p>

<p align="center">
  <a href="https://adivinheganhe.com.br" target="_blank"><strong>🌐 Acesse o site oficial</strong></a>
</p>

<hr>

<h2>📌 Sobre o Projeto</h2>

<p>
  <strong>Adivinhe e Ganhe</strong> é um projeto brasileiro de código aberto criado com a missão de ser justo, seguro e divertido.
  Ele não é uma plataforma de apostas — a ideia é oferecer uma experiência lúdica e saudável. Futuramente, pretendemos torná-lo totalmente gratuito.
</p>

<p>
  Atualmente vendemos <b>dicas</b> e <b>palpites</b> apenas para manter o projeto online. 
  No jogo, o usuário tenta adivinhar corretamente um item, que pode ser uma imagem, vídeo ou áudio. Em caso de acerto, ele é premiado!
</p>

<h2>🚀 Tecnologias Utilizadas</h2>

<ul>
  <li><b>PHP 8.2+</b></li>
  <li><b>Laravel</b> (framework principal)</li>
  <li><b>Laravel Reverb</b> (WebSocket para comunicação em tempo real)</li>
  <li><b>Octane + Swoole</b> (ambiente de produção)</li>
</ul>

<p><i>Em breve: migração para <b>Swoole</b> para performance ainda melhor!</i></p>

<h2>📦 Instalação</h2>

<ol>
  <li>Clone o projeto:<br><code>git clone https://github.com/PedroShimpa/adivinheganhe</code></li>
  <li>Entre na pasta:<br><code>cd adivinheganhe</code></li>
  <li>Copie o arquivo de ambiente:<br><code>cp .env.example .env</code></li>
  <li>Instale as dependências:<br><code>composer install</code></li>
  <li>Gere a chave da aplicação:<br><code>php artisan key:generate</code></li>
  <li>Insira as tabelas essenciais:<br><code>php artisan migrate</code></li> 
  <li>Crie o link simbólico para o storage:<br><code>php artisan storage:link</code></li>
  <li>Inicie o reverb com <br><code>php artisan reverb:start</code></li>
  <li>Inicie o projeto com <br><code>php artisan serve</code></li>
  <li>O sistema depende MUITO de queues, então não esqueça do: php artisan queue:work!</li>
</ol>
<p>
  Para cadastrar um colaborador como ADM, você precisa fazer isso direto no banco de dados na tabela user, sete a coluna `is_admin` de "N" para "S" no usuario desejado.
</p>
<p>
  Para desemepnho maximo, estamos usando cache storage, então sempre que fizer uma alteração diretamente no banco não esqueça de executar: <code>php artisan cache:clear</code>
</p>

<h2>💡 Contribuições</h2>

<p>
  Ainda estamos aprimorando a segurança e o desempenho da plataforma.<br>
  Sinta-se à vontade para abrir uma <b>issue</b> ou enviar um <b>pull request</b>. Toda ajuda é bem-vinda! 🙌
</p>

<h2>🔗 Links</h2>

<ul>
  <li><a href="https://adivinheganhe.com.br" target="_blank">🌐 Site Oficial</a></li>
  <li><a href="https://github.com/PedroShimpa/adivinheganhe" target="_blank">📁 Repositório no GitHub</a></li>
</ul>

<h2>Para o futuro</h2>

<ul>
  <li>Notificações automaticas via whatsapp usando WppConnect server</li>
  <li>Incluir testes de todas as fuções</li>
</ul>

<h2>Estrutura</h2>

<p>
 A ideia do projeto é ser extremamente rapido e competitivo, então estamos usando redis para cache, queues e afins, futuramente protendo inserir as respostas em um banco de dados cassandra por escalar melhor que bancos de dados SQL, por enquanto estamos rodando em um servidor da locaweb com 1Gb ram e 2 Vcpus, a locaweb não é o melhor provedor para sistemas que exigem milhares de requisções, mas por enquanto é o que esta oferecendo melhor custo para seguimento do projeto.
</p>
<hr>

<p align="center">
  Feito com ❤️ no Brasil &nbsp;|&nbsp; <i>Aposte no seu palpite, não no seu dinheiro</i>
</p>

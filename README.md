
# 📚 Sistema Bibliotecário V3 - Customização

Este repositório contém a versão **personalizada** do sistema de código aberto **Bibliotecário V3**, adaptada para o **Colégio Piamarta Montese**.  
Foram realizadas edições no **front-end e back-end**, adequando o sistema à identidade visual do colégio e adicionando novas funcionalidades.

---

## 🚀 Sobre o Projeto
O **Bibliotecário V3** é um sistema gratuito para administração de bibliotecas, desenvolvido em **PHP** e **SQLite3**.  
Este fork foi modificado para:

- 🎨 Adequação da **identidade visual** ao colégio.  
- ⚙️ Melhorias no **front-end** e **back-end**.  
- 🆕 Adição de novas funcionalidades (em andamento).
- 🔄 Ultima atualização: Adição do da função e do relatório de leitores do mês.

Projeto original: [Online Escola](https://www.onlineescola.com.br/).

---

## 🛠️ Tecnologias Utilizadas
- PHP 7+  
- Sqlite3  
- HTML, CSS e JavaScript  
- Bootstrap
- Node.js 

---

## 📂 Estrutura do Projeto

Abaixo a organização principal da pasta `www`:

````
📂 www/
├── 📂 admin/              -> Área administrativa do sistema
├── 📂 arquivos/           -> Armazenamento de documentos/arquivos do sistema
├── 📂 data/               -> Banco de dados (.db) //IGNORAR NO GIT//
├── 📂 img/                -> Imagens utilizadas no sistema (logos, ícones, etc.)
├── 📂 node_modules/       -> Dependências do Node.js 
├── 📂 perfil/             -> Páginas relacionadas ao perfil de usuários
├── 📂 uploads/            -> Pasta para uploads (fotos de usuários, capas de livros, etc.) //IGNORAR NO GIT//
├── 📂 usuarios/           -> Gestão de usuários e cadastros relacionados
│
├── index.php              -> Página inicial / tela de login
├── info.php               -> Exibe informações do PHP (debug/versão)
├── open_external.php      -> Script auxiliar para abrir arquivos externos
├── painel.php             -> Painel principal / dashboard do sistema
├── sair.php               -> Script de logout
│
├── package.json -> Dependências e scripts do projeto (Node.js)
└── package-lock.json -> Versões travadas das dependências
````
> ⚠️ **Observação**:  
> - As pastas `uploads/` e `arquivos/` precisam de **permissão de escrita** no servidor.  
> - O arquivo `data/` deve conter o banco de dados SQlite exportado (.db).  
> - O ponto de entrada principal do sistema é o `index.php`.  

---

## ⚙️ Instalação e Produção

O **Bibliotecário V3** pode ser instalado de forma simples usando o instalador oficial do projeto:

1. Baixe o instalador disponibilizado pelo autor:

   👉 [Download Bibliotecário V3](https://www.onlineescola.com.br/2025/04/bibliotecario-v3-o-sistema-ideal-para.html)

2. Execute o arquivo:
````
install-bibliotecario-v3.exe
````
Este instalador prepara automaticamente:
- Servidor Apache  
- PHP  
- Banco de dados MySQL/MariaDB  
- Pasta padrão `www/`  

3. Após a instalação, substitua a pasta `www` original pela versão personalizada disponível neste repositório.

4. Acesse o sistema no navegador:
````
http://localhost/
````

---

### 📌 Observações de Produção

- Se for usar em **servidor real** (fora do instalador), instale manualmente **Apache + PHP + SQLite** e copie a pasta `www` para a raiz do servidor.  
- Verifique as permissões das pastas `uploads/` e `arquivos/`.   

---

## 👨‍💻 Autor
- Projeto original: [Online Escola](https://www.onlineescola.com.br/)  
- Customizações: **LEONARDO AZEVEDO**

---

📌 Este repositório serve como documentação e versionamento das melhorias aplicadas no sistema para o uso interno do colégio.


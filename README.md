# Atividade Avaliativa 2 - Testes de Integracao

**Aluno:** Iago Andrade

## Objetivo da Atividade

Esta atividade tem como objetivo implementar e documentar testes de integracao em um projeto Laravel, validando fluxos reais da aplicacao por meio de requisicoes HTTP, rotas, controllers, models, migrations e banco de dados de teste.

Os testes cobrem os principais comportamentos das entidades **Autores**, **Livros** e **Pessoas**, incluindo cadastro, validacoes, atualizacao, edicao de registros inexistentes e exclusao quando implementada.

## Tecnologias Utilizadas

- Laravel
- PHP
- PHPUnit
- MySQL
- Docker
- GitHub Actions

## Como Executar o Projeto

Construir as imagens Docker:

```bash
docker compose build --no-cache
```

Iniciar o ambiente:

```bash
docker compose up
```

Executar comandos Artisan dentro do container da aplicacao:

```bash
docker compose exec app php artisan <comando>
```

Exemplo para executar migrations:

```bash
docker compose exec app php artisan migrate
```

Parar o ambiente:

```bash
docker compose down
```

## Como Executar os Testes

Executar todos os testes:

```bash
php artisan test
```

Executar somente os testes de autores:

```bash
php artisan test --filter AutorTest
```

Executar somente os testes de livros:

```bash
php artisan test --filter LivroTest
```

Executar somente os testes de pessoas:

```bash
php artisan test --filter PessoaTest
```

## Testes de Autores

Arquivo: `tests/Feature/AutorTest.php`

Os testes de autores validam:

- criacao de autor com dados validos;
- obrigatoriedade do campo `nome`;
- limite maximo de 200 caracteres para o campo `nome`;
- atualizacao valida de um autor existente;
- bloqueio de atualizacao invalida quando o nome nao e informado;
- tentativa de edicao de autor inexistente, retornando 404.

## Testes de Livros

Arquivo: `tests/Feature/LivroTest.php`

Os testes de livros validam:

- criacao de livro com dados validos;
- exibicao de livro existente;
- retorno 404 ao acessar livro inexistente;
- atualizacao valida de um livro existente;
- exclusao valida de um livro existente.

O teste de exibicao de livro existente esta marcado com `markTestSkipped()`, pois a rota `livros.show` chama a view `resources/views/livros/show.blade.php`, que nao existe no projeto. Sem essa view, a requisicao retorna erro 500.

## Testes de Pessoas

Arquivo: `tests/Feature/PessoaTest.php`

Os testes de pessoas validam:

- criacao de pessoa com dados validos;
- bloqueio de cadastro quando `password` e `confirmPassword` sao diferentes;
- atualizacao valida de uma pessoa existente;
- bloqueio de atualizacao quando as senhas sao diferentes;
- tentativa de edicao de pessoa inexistente;
- exclusao de pessoa.

Os testes tambem verificam que a senha e salva com hash, utilizando `Hash::check`.

O teste de exclusao de pessoa esta marcado com `markTestSkipped()`, pois o metodo `PessoaController::destroy()` esta vazio. Assim, a rota `DELETE /pessoas/{id}` nao remove o registro nem redireciona apos a exclusao.

## Resultado Atual

- 17 testes passando.
- 2 testes ignorados.
- GitHub Actions executando com sucesso.

Os dois testes ignorados correspondem a problemas conhecidos do projeto e possuem justificativa diretamente no codigo por meio de `markTestSkipped()`.

## Problemas Encontrados

- A rota de exibicao de um livro existente retorna erro 500 porque `resources/views/livros/show.blade.php` nao existe.
- `PessoaController::destroy()` esta vazio, entao a exclusao de pessoas ainda nao foi implementada.
- Existem inconsistencias nos campos `$fillable` dos models em relacao as migrations e controllers. Por exemplo, `Autor` possui `sobrenome` no `$fillable`, mas esse campo nao aparece na migration; `Livro` possui campos como `autor`, `editora` e `ano_publicacao`, enquanto a migration e o controller trabalham com `autor_id`, `titulo`, `isbn` e `data_publicacao`.
- O ambiente original de testes estava configurado para SQLite, mas o container nao possuia o driver necessario. Para a execucao atual, os testes usam MySQL com o banco `app_biblioteca_test`.

## GitHub Actions

O workflow de integracao continua esta definido em:

```text
.github/workflows/tests.yml
```

Ele executa automaticamente os testes em:

- `pull_request`;
- `push` para as branches `master`, `develop` e `testes-integracao-iago`.

O workflow utiliza Ubuntu, PHP 8.4 e MySQL 8.4. As etapas principais sao:

- checkout do repositorio;
- configuracao do PHP 8.4 com extensoes necessarias;
- instalacao das dependencias com Composer;
- inicializacao e espera do MySQL;
- execucao das migrations com `php artisan migrate --force`;
- execucao da suite de testes com `php artisan test`.

O GitHub Actions nao utiliza Docker Compose. O banco MySQL e criado como servico proprio do workflow.

<img width="1117" height="963" alt="image" src="https://github.com/user-attachments/assets/582989e5-51be-451a-ba35-e8c84f17fac7" />


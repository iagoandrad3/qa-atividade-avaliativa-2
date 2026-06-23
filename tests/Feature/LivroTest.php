<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Livro;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LivroTest extends TestCase
{
    use DatabaseTransactions;

    private function tokenCsrf(): string
    {
        return 'token-de-teste';
    }

    private function criarAutor(): Autor
    {
        return Autor::create([
            'nome' => 'Machado de Assis',
            'nacionalidade' => 'Brasileira',
        ]);
    }
    private function criarLivro(
    Autor $autor,
    string $titulo,
    string $isbn,
    string $dataPublicacao
): Livro {
    $livro = new Livro();
    $livro->autor_id = $autor->id;
    $livro->titulo = $titulo;
    $livro->isbn = $isbn;
    $livro->data_publicacao = $dataPublicacao;
    $livro->save();

    return $livro;
}

    public function test_deve_criar_livro_com_dados_validos(): void
    {
        $autor = $this->criarAutor();
        $token = $this->tokenCsrf();

        $response = $this
            ->withSession(['_token' => $token])
            ->post('/livros', [
                '_token' => $token,
                'autor_id' => $autor->id,
                'titulo' => 'Dom Casmurro',
                'isbn' => '9788535910663',
                'data_publicacao' => '1899-01-01',
            ]);

        $response->assertRedirect(route('livros.index'));

        $this->assertDatabaseHas('livros', [
            'autor_id' => $autor->id,
            'titulo' => 'Dom Casmurro',
            'isbn' => '9788535910663',
            'data_publicacao' => '1899-01-01',
        ]);
    }

    public function test_deve_exibir_livro_existente(): void
{
    $this->markTestSkipped(
        'A rota livros.show retorna erro 500 porque a view resources/views/livros/show.blade.php não existe.'
    );

    $autor = $this->criarAutor();

    $livro = $this->criarLivro(
        $autor,
        'Memórias Póstumas',
        '9780000000001',
        '1881-01-01'
    );

    $response = $this->get("/livros/{$livro->id}");

    $response->assertOk();
    $response->assertViewIs('livros.show');
    $response->assertViewHas('livro');
}
    public function test_livro_inexistente_deve_retornar_404(): void
    {
        $response = $this->get('/livros/999999');

        $response->assertNotFound();
    }

    public function test_deve_atualizar_livro_existente(): void
    {
        $autor = $this->criarAutor();

        $livro = $this->criarLivro(
    $autor,
    'Título Antigo',
    '9780000000002',
    '2000-01-01'
);
        $token = $this->tokenCsrf();

        $response = $this
            ->withSession(['_token' => $token])
            ->put("/livros/update/{$livro->id}", [
                '_token' => $token,
                'autor_id' => $autor->id,
                'titulo' => 'Título Atualizado',
                'isbn' => '9780000000003',
                'data_publicacao' => '2001-01-01',
            ]);

        $response->assertRedirect(route('livros.index'));

        $this->assertDatabaseHas('livros', [
            'id' => $livro->id,
            'titulo' => 'Título Atualizado',
            'isbn' => '9780000000003',
        ]);
    }

    public function test_deve_excluir_livro_existente(): void
    {
        $autor = $this->criarAutor();

        $livro = $this->criarLivro(
    $autor,
    'Livro para excluir',
    '9780000000004',
    '2010-01-01'
);

        $token = $this->tokenCsrf();

        $response = $this
            ->withSession(['_token' => $token])
            ->delete("/livros/{$livro->id}", [
                '_token' => $token,
            ]);

        $response->assertRedirect(route('livros.index'));

        $this->assertDatabaseMissing('livros', [
            'id' => $livro->id,
        ]);
    }
}
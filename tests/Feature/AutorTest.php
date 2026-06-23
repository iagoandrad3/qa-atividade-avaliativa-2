<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AutorTest extends TestCase
{
    use DatabaseTransactions;

    private function tokenCsrf(): string
    {
        return 'token-de-teste';
    }

    public function test_deve_criar_autor_com_dados_validos(): void
    {
        $token = $this->tokenCsrf();

        $response = $this
            ->withSession(['_token' => $token])
            ->post('/autores', [
                '_token' => $token,
                'nome' => 'Machado de Assis',
                'nacionalidade' => 'Brasileira',
            ]);

        $response->assertRedirect(route('autores.index'));

        $this->assertDatabaseHas('autores', [
            'nome' => 'Machado de Assis',
            'nacionalidade' => 'Brasileira',
        ]);
    }

    public function test_nao_deve_criar_autor_sem_nome(): void
    {
        $token = $this->tokenCsrf();

        $response = $this
            ->withSession(['_token' => $token])
            ->from('/autores/create')
            ->post('/autores', [
                '_token' => $token,
                'nome' => '',
                'nacionalidade' => 'Brasileira',
            ]);

        $response->assertRedirect('/autores/create');
        $response->assertSessionHasErrors(['nome']);

        $this->assertDatabaseMissing('autores', [
            'nome' => '',
            'nacionalidade' => 'Brasileira',
        ]);
    }

    public function test_nao_deve_criar_autor_com_nome_maior_que_200_caracteres(): void
    {
        $token = $this->tokenCsrf();
        $nomeMuitoGrande = str_repeat('A', 201);

        $response = $this
            ->withSession(['_token' => $token])
            ->from('/autores/create')
            ->post('/autores', [
                '_token' => $token,
                'nome' => $nomeMuitoGrande,
                'nacionalidade' => 'Brasileira',
            ]);

        $response->assertRedirect('/autores/create');
        $response->assertSessionHasErrors(['nome']);

        $this->assertDatabaseMissing('autores', [
            'nome' => $nomeMuitoGrande,
        ]);
    }
    public function test_deve_atualizar_um_autor_existente(): void
{
    $autor = \App\Models\Autor::create([
        'nome' => 'Nome Antigo',
        'nacionalidade' => 'Brasileira',
    ]);

    $token = $this->tokenCsrf();

    $response = $this
        ->withSession(['_token' => $token])
        ->put("/autores/update/{$autor->id}", [
            '_token' => $token,
            'nome' => 'Nome Atualizado',
            'nacionalidade' => 'Portuguesa',
        ]);

    $response->assertRedirect(route('autores.index'));

    $this->assertDatabaseHas('autores', [
        'id' => $autor->id,
        'nome' => 'Nome Atualizado',
        'nacionalidade' => 'Portuguesa',
    ]);
}

public function test_nao_deve_atualizar_autor_sem_nome(): void
{
    $autor = \App\Models\Autor::create([
        'nome' => 'Autor Original',
        'nacionalidade' => 'Brasileira',
    ]);

    $token = $this->tokenCsrf();

    $response = $this
        ->withSession(['_token' => $token])
        ->from("/autores/edit/{$autor->id}")
        ->put("/autores/update/{$autor->id}", [
            '_token' => $token,
            'nome' => '',
            'nacionalidade' => 'Brasileira',
        ]);

    $response->assertRedirect("/autores/edit/{$autor->id}");
    $response->assertSessionHasErrors(['nome']);

    $this->assertDatabaseHas('autores', [
        'id' => $autor->id,
        'nome' => 'Autor Original',
    ]);
}

public function test_edicao_de_autor_inexistente_deve_retornar_404(): void
{
    $response = $this->get('/autores/edit/999999');

    $response->assertNotFound();
}
}
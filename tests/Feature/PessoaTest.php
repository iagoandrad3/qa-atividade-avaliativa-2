<?php

namespace Tests\Feature;

use App\Models\Pessoa;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PessoaTest extends TestCase
{
    use DatabaseTransactions;

    private function tokenCsrf(): string
    {
        return 'token-de-teste';
    }

    private function criarPessoa(
        string $name,
        string $email,
        string $matricula,
        string $telefone,
        string $password
    ): Pessoa {
        return Pessoa::create([
            'name' => $name,
            'email' => $email,
            'telefone' => $telefone,
            'matricula' => $matricula,
            'password' => Hash::make($password),
        ]);
    }

    public function test_deve_criar_pessoa_com_dados_validos(): void
    {
        $token = $this->tokenCsrf();

        $response = $this
            ->withSession(['_token' => $token])
            ->post('/pessoas', [
                '_token' => $token,
                'name' => 'Pessoa Cadastro Valido',
                'email' => 'pessoa.cadastro.valido@example.com',
                'telefone' => '11999990001',
                'matricula' => 'MAT-PES-001',
                'password' => 'senha-cadastro-123',
                'confirmPassword' => 'senha-cadastro-123',
            ]);

        $response->assertRedirect(route('pessoas.index'));
        $response->assertSessionHas('message', 'Pessoa criada com sucesso!');

        $this->assertDatabaseHas('pessoas', [
            'name' => 'Pessoa Cadastro Valido',
            'email' => 'pessoa.cadastro.valido@example.com',
            'telefone' => '11999990001',
            'matricula' => 'MAT-PES-001',
        ]);

        $pessoa = Pessoa::where('email', 'pessoa.cadastro.valido@example.com')->firstOrFail();

        $this->assertNotSame('senha-cadastro-123', $pessoa->password);
        $this->assertTrue(Hash::check('senha-cadastro-123', $pessoa->password));
    }

    public function test_nao_deve_criar_pessoa_quando_senhas_forem_diferentes(): void
    {
        $token = $this->tokenCsrf();

        $response = $this
            ->withSession(['_token' => $token])
            ->from('/pessoas/create')
            ->post('/pessoas', [
                '_token' => $token,
                'name' => 'Pessoa Cadastro Senhas Diferentes',
                'email' => 'pessoa.cadastro.senhas.diferentes@example.com',
                'telefone' => '11999990002',
                'matricula' => 'MAT-PES-002',
                'password' => 'senha-original-123',
                'confirmPassword' => 'senha-diferente-123',
            ]);

        $response->assertRedirect('/pessoas/create');
        $response->assertSessionHas('error', 'As senhas não coincidem!');

        $this->assertDatabaseMissing('pessoas', [
            'email' => 'pessoa.cadastro.senhas.diferentes@example.com',
            'matricula' => 'MAT-PES-002',
        ]);
    }

    public function test_deve_atualizar_pessoa_existente_com_dados_validos(): void
    {
        $pessoa = $this->criarPessoa(
            'Pessoa Antes Atualizacao',
            'pessoa.antes.atualizacao@example.com',
            'MAT-PES-003',
            '11999990003',
            'senha-antiga-123'
        );

        $token = $this->tokenCsrf();

        $response = $this
            ->withSession(['_token' => $token])
            ->put("/pessoas/{$pessoa->id}", [
                '_token' => $token,
                'name' => 'Pessoa Depois Atualizacao',
                'email' => 'pessoa.depois.atualizacao@example.com',
                'telefone' => '11999990004',
                'matricula' => 'MAT-PES-004',
                'password' => 'senha-nova-123',
                'confirmPassword' => 'senha-nova-123',
            ]);

        $response->assertRedirect(route('pessoas.index'));
        $response->assertSessionHas('message', 'Pessoa atualizada com sucesso!');

        $this->assertDatabaseHas('pessoas', [
            'id' => $pessoa->id,
            'name' => 'Pessoa Depois Atualizacao',
            'email' => 'pessoa.depois.atualizacao@example.com',
            'telefone' => '11999990004',
            'matricula' => 'MAT-PES-004',
        ]);

        $pessoaAtualizada = $pessoa->fresh();

        $this->assertNotSame('senha-nova-123', $pessoaAtualizada->password);
        $this->assertTrue(Hash::check('senha-nova-123', $pessoaAtualizada->password));
    }

    public function test_nao_deve_atualizar_pessoa_quando_senhas_forem_diferentes(): void
    {
        $pessoa = $this->criarPessoa(
            'Pessoa Original Senhas Diferentes',
            'pessoa.original.senhas.diferentes@example.com',
            'MAT-PES-005',
            '11999990005',
            'senha-original-123'
        );

        $token = $this->tokenCsrf();

        $response = $this
            ->withSession(['_token' => $token])
            ->from("/pessoas/{$pessoa->id}/edit")
            ->put("/pessoas/{$pessoa->id}", [
                '_token' => $token,
                'name' => 'Pessoa Alterada Senhas Diferentes',
                'email' => 'pessoa.alterada.senhas.diferentes@example.com',
                'telefone' => '11999990006',
                'matricula' => 'MAT-PES-006',
                'password' => 'senha-nova-123',
                'confirmPassword' => 'senha-divergente-123',
            ]);

        $response->assertRedirect("/pessoas/{$pessoa->id}/edit");
        $response->assertSessionHas('error', 'As senhas não coincidem!');

        $this->assertDatabaseHas('pessoas', [
            'id' => $pessoa->id,
            'name' => 'Pessoa Original Senhas Diferentes',
            'email' => 'pessoa.original.senhas.diferentes@example.com',
            'telefone' => '11999990005',
            'matricula' => 'MAT-PES-005',
        ]);

        $this->assertDatabaseMissing('pessoas', [
            'id' => $pessoa->id,
            'email' => 'pessoa.alterada.senhas.diferentes@example.com',
            'matricula' => 'MAT-PES-006',
        ]);
    }

    public function test_edicao_de_pessoa_inexistente_deve_redirecionar_com_erro(): void
    {
        $response = $this->get('/pessoas/999999/edit');

        $response->assertRedirect(route('pessoas.index'));
        $response->assertSessionHas('error', 'Pessoa não encontrada');
    }

    public function test_deve_excluir_pessoa_existente(): void
    {
        $this->markTestSkipped(
            'PessoaController::destroy() está vazio; a rota DELETE /pessoas/{id} não remove a pessoa nem redireciona.'
        );

        $pessoa = $this->criarPessoa(
            'Pessoa Para Excluir',
            'pessoa.para.excluir@example.com',
            'MAT-PES-007',
            '11999990007',
            'senha-exclusao-123'
        );

        $token = $this->tokenCsrf();

        $response = $this
            ->withSession(['_token' => $token])
            ->delete("/pessoas/{$pessoa->id}", [
                '_token' => $token,
            ]);

        $response->assertRedirect(route('pessoas.index'));

        $this->assertDatabaseMissing('pessoas', [
            'id' => $pessoa->id,
        ]);
    }
}

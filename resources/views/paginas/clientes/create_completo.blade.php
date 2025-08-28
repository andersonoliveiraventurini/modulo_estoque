<x-layouts.app :title="__('Cadastrar cliente - completo')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <div class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                  
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-icon name="user" class="w-5 h-5 text-primary-600" />
                    Cadastro de Cliente
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                    Preencha as informações do cliente para realizar o cadastro.
                </p>

                <form action="{{ route('clientes.store') }}" method="POST" enctype="multipart/form-data" class="space-y-10">
                    @csrf

                    <!-- ==========================
                         DADOS DO CLIENTE (PJ)
                    =========================== -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium">Dados do Cliente (Pessoa Jurídica)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input name="razao_social" label="Razão Social *" />
                            <x-input name="nome_fantasia" label="Nome Fantasia" />
                            <x-input name="tratamento" label="Tratamento" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input name="desconto" label="Desconto (%)" type="number" step="0.01"/>
                            <x-input name="cnpj" label="CNPJ *" placeholder="00.000.000/0000-00"/>
                            <x-input name="inscricao_estadual" label="Inscrição Estadual"/>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input name="inscricao_municipal" label="Inscrição Municipal"/>
                            <x-input type="date" name="data_abertura" label="Data de Abertura"/>
                            <x-input name="cnae" label="CNAE Principal"/>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-select name="regime_tributario" label="Regime Tributário">
                                <option value="simples">Simples Nacional</option>
                                <option value="lucro_presumido">Lucro Presumido</option>
                                <option value="lucro_real">Lucro Real</option>
                            </x-select>
                        </div>
                    </div>

                    <!-- Endereço -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium">Endereço</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input name="cep" label="CEP"/>
                            <x-input name="cidade" label="Cidade"/>
                            <x-input name="estado" label="Estado"/>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input name="endereco" label="Endereço Completo"/>
                            <x-input name="endereco_entrega" label="Endereço de Entrega"/>
                        </div>
                    </div>

                    <!-- Contatos -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium">Contatos</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input name="contato" label="Contato"/>
                            <x-input name="funcao_contato" label="Função do Contato"/>
                            <x-input name="contato_financeiro" label="Contato Financeiro"/>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input name="telefone_empresa" label="Telefone Empresa"/>
                            <x-input name="telefone_contato" label="Telefone Contato"/>
                            <x-input name="telefone_financeiro" label="Telefone Financeiro"/>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input type="email" name="email_xml" label="E-mail XML"/>
                            <x-input type="email" name="email_cobranca" label="E-mail Cobrança"/>
                        </div>
                    </div>

                    <!-- Responsável e Documentos -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium">Responsável Legal</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input name="cpf_responsavel" label="CPF do Responsável"/>
                            <x-input type="file" name="certidoes_negativas" label="Certidões Negativas"/>
                            <x-input name="suframa" label="Inscrição SUFRAMA (se aplicável)"/>
                        </div>
                    </div>

                    <!-- Classificação -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium">Classificação</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-select name="classificacao" label="Classificação">
                                <option>Distribuidor</option>
                                <option>Revenda</option>
                                <option>Construtora</option>
                                <option>Consumidor Final</option>
                            </x-select>
                            <x-select name="canal_origem" label="Canal de Origem">
                                <option>Indicação</option>
                                <option>Marketing</option>
                                <option>Visita</option>
                            </x-select>
                            <x-select name="status" label="Status">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="bloqueado">Bloqueado</option>
                            </x-select>
                        </div>
                        <x-input type="number" name="inativar_apos" label="Inativar automaticamente após (meses sem comprar)"/>
                    </div>

                    <!-- Informações de Crédito -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium">Informações de Crédito</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-checkbox name="credito_a_vista" label="À Vista"/>
                            <x-input type="number" step="0.01" name="limite_boleto" label="Limite Boleto (R$)"/>
                            <x-input type="number" step="0.01" name="limite_carteira" label="Limite Carteira (R$)"/>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input type="date" name="data_ultima_analise" label="Data da Última Análise"/>
                            <x-input type="date" name="data_vencimento_analise" label="Data Vencimento da Análise"/>
                        </div>
                        <x-textarea name="historico_credito" label="Histórico de Análise de Crédito"/>
                        <x-checkbox name="nao_negociar_titulos" label="Não aceitar negociar títulos"/>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-checkbox name="bloqueio" label="Bloqueado"/>
                            <x-input name="motivo_bloqueio" label="Motivo do Bloqueio"/>
                            <x-input type="date" name="data_bloqueio" label="Data Bloqueio"/>
                        </div>
                    </div>

                    <!-- Observações -->
                    <div>
                        <x-textarea name="observacoes" label="Observações" placeholder="Informações adicionais..."/>
                    </div>

                    <!-- Ações -->
                    <div class="flex gap-4">
                        <x-button type="submit" class="bg-primary-600 text-white">Cadastrar Cliente</x-button>
                        <x-button type="reset">Limpar</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        async function buscarCNPJ() {
    const cnpj = document.querySelector('[name="cnpj"]').value.replace(/\D/g, ""); // remove caracteres não numéricos

    if (!cnpj) {
        alert("Digite um CNPJ válido");
        return;
    }

    try {
        const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`);
        if (!response.ok) throw new Error("Erro ao consultar CNPJ");

        const data = await response.json();

        // Dados principais
        document.querySelector('[name="razao_social"]').value = data.razao_social || "";
        document.querySelector('[name="nome_fantasia"]').value = data.nome_fantasia || "";
        document.querySelector('[name="cnae"]').value = data.cnae_fiscal_descricao || "";
        document.querySelector('[name="data_abertura"]').value = data.data_inicio_atividade || "";

        // Endereço
        document.querySelector('[name="cep"]').value = data.cep || "";
        document.querySelector('[name="cidade"]').value = data.municipio || "";
        document.querySelector('[name="estado"]').value = data.uf || "";
        document.querySelector('[name="endereco"]').value = 
            `${data.descricao_tipo_de_logradouro || ""} ${data.logradouro || ""}, ${data.numero || ""} ${data.complemento || ""}`.trim();

        // Telefones (a API retorna até dois)
        document.querySelector('[name="telefone_empresa"]').value = data.ddd_telefone_1 || "";
        document.querySelector('[name="telefone_contato"]').value = data.ddd_telefone_2 || "";

        // Emails
        document.querySelector('[name="email_xml"]').value = data.email || "";

        // Regime tributário (simplificado: escolhe o primeiro válido)
        if (data.regime_tributario && data.regime_tributario.length > 0) {
            const regime = data.regime_tributario[0].forma_de_tributacao?.toLowerCase();
            const select = document.querySelector('[name="regime_tributario"]');
            if (select) {
                if (regime.includes("simples")) {
                    select.value = "simples";
                } else if (regime.includes("presumido")) {
                    select.value = "lucro_presumido";
                } else if (regime.includes("real")) {
                    select.value = "lucro_real";
                }
            }
        }

        // Responsável legal (primeiro da lista QSA)
        if (data.qsa && data.qsa.length > 0) {
            document.querySelector('[name="cpf_responsavel"]').value = data.qsa[0].cnpj_cpf_do_socio || "";
        }

    } catch (err) {
        alert("Erro: " + err.message);
    }
}

// opcional: chama a função quando sair do campo CNPJ
document.addEventListener("DOMContentLoaded", () => {
    const cnpjInput = document.querySelector('[name="cnpj"]');
    if (cnpjInput) {
        cnpjInput.addEventListener("blur", buscarCNPJ);
    }
});
</script>
</x-layouts.app>

async function buscarCNPJ() {
    const cnpj = document.getElementById("cnpj").value.replace(/\D/g, "");
    if (cnpj.length !== 14) {
        alert("CNPJ inválido!");
        return;
    }

    try {
        const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`);
        if (!response.ok) throw new Error("Erro ao consultar CNPJ");

        const data = await response.json();

        // dados principais
        document.querySelector('[name="razao_social"]').value = data.razao_social || "";
        document.querySelector('[name="nome_fantasia"]').value = data.nome_fantasia || "";
        
        // campos especificios completo
        document.querySelector('[name="data_abertura"]').value = data.data_inicio_atividade || "";
        document.querySelector('[name="cnae"]').value = data.cnae_fiscal_descricao || "";

        // endereço
        document.getElementById("endereco_logradouro").value = data.logradouro || "";
        document.getElementById("endereco_bairro").value = data.bairro || "";
        document.getElementById("endereco_cidade").value = data.municipio || "";
        document.getElementById("endereco_estado").value = data.uf || "";
        document.getElementById("endereco_cep").value = data.cep || "";
      
        // Contato
        if (data.ddd_telefone_1) {
            const telefone = `(${data.ddd_telefone_1.substring(0, 2)}) ${data.ddd_telefone_1.substring(2)}`;
            document.querySelector("[name='contatos[0][telefone]']").value = telefone;
        }
        if (data.email) {
            document.querySelector("[name='contatos[0][email]']").value = data.email;
        }

        // Opcional: sócios (primeiro da lista)
        if (data.qsa && data.qsa.length > 0) {
            document.querySelector("[name='contatos[0][nome]']").value = data.qsa[0].nome_socio || "";
        }

    } catch (err) {
        alert("Erro: " + err.message);
    }
}

async function buscarCNPJ_precadastro() {
    const cnpj = document.getElementById("cnpj").value.replace(/\D/g, "");
    if (cnpj.length !== 14) {
        alert("CNPJ inválido!");
        return;
    }

    try {
        const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`);
        if (!response.ok) throw new Error("Erro ao consultar CNPJ");

        const data = await response.json();

        // dados principais
        document.querySelector('[name="razao_social"]').value = data.razao_social || "";
        document.querySelector('[name="nome_fantasia"]').value = data.nome_fantasia || "";
        //document.querySelector('[name="email"]').value = data.email || "";

        // campos especificios completo
        //document.querySelector('[name="data_abertura"]').value = data.data_inicio_atividade || "";
        //document.querySelector('[name="cnae"]').value = data.cnae_fiscal_descricao || "";

        // endereço
        document.getElementById("endereco_logradouro").value = data.logradouro || "";
        document.getElementById("endereco_bairro").value = data.bairro || "";
        document.getElementById("endereco_cidade").value = data.municipio || "";
        document.getElementById("endereco_estado").value = data.uf || "";
        document.getElementById("endereco_cep").value = data.cep || "";
      
        // Contato
        if (data.ddd_telefone_1) {
            const telefone = `(${data.ddd_telefone_1.substring(0, 2)}) ${data.ddd_telefone_1.substring(2)}`;
            document.querySelector("[name='contatos[0][telefone]']").value = telefone;
        }
        if (data.email) {
            document.querySelector("[name='contatos[0][email]']").value = data.email;
        }

        // Opcional: sócios (primeiro da lista)
        if (data.qsa && data.qsa.length > 0) {
            document.querySelector("[name='contatos[0][nome]']").value = data.qsa[0].nome_socio || "";
        }

    } catch (err) {
        alert("Erro: " + err.message);
    }
}

async function buscarCNPJfornecedor() {
    const cnpj = document.getElementById("cnpj").value.replace(/\D/g, "");
    if (cnpj.length !== 14) {
        alert("CNPJ inválido!");
        return;
    }

    try {
        const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`);
        if (!response.ok) throw new Error("Erro ao consultar CNPJ");

        const data = await response.json();

        // função auxiliar segura
        const setValue = (selector, value) => {
            const el = document.querySelector(selector);
            if (el && value) el.value = value;
        };

        const formatDate = (dateStr) => dateStr ? new Date(dateStr).toISOString().split("T")[0] : "";

        // Dados principais
        setValue('[name="razao_social"]', data.razao_social);
        setValue('[name="nome_fantasia"]', data.nome_fantasia);
        setValue('[name="data_abertura"]', formatDate(data.data_inicio_atividade));
        setValue('[name="cnae_principal"]', `${data.cnae_fiscal} - ${data.cnae_fiscal_descricao}`);

        // Regime tributário
        if (data.regime_tributario?.length) {
            const ultimo = data.regime_tributario[data.regime_tributario.length - 1].forma_de_tributacao;
            const regimeMap = {
                "SIMPLES NACIONAL": "simples",
                "LUCRO PRESUMIDO": "lucro_presumido",
                "LUCRO REAL": "lucro_real"
            };
            setValue('[name="regime_tributario"]', regimeMap[ultimo.toUpperCase()] || "");
        }

        // Status
        if (data.descricao_situacao_cadastral) {
            let status = "inativo";
            if (data.descricao_situacao_cadastral.toUpperCase() === "ATIVA") status = "ativo";
            else if (data.descricao_situacao_cadastral.toUpperCase().includes("BLOQUE")) status = "bloqueado";
            setValue('[name="status"]', status);
        }

        // Endereço (usar setValue ao invés de getElementById direto)
        setValue('[name="endereco_logradouro"]', data.logradouro);
        setValue('[name="endereco_bairro"]', data.bairro);
        setValue('[name="endereco_cidade"]', data.municipio);
        setValue('[name="endereco_estado"]', data.uf);
        setValue('[name="endereco_cep"]', data.cep?.replace(/^(\d{5})(\d{3})$/, "$1-$2"));

        // Contato
        if (data.ddd_telefone_1) {
            const tel = `(${data.ddd_telefone_1.substring(0, 2)}) ${data.ddd_telefone_1.substring(2)}`;
            setValue("[name='contatos[0][telefone]']", tel);
        }
        setValue("[name='contatos[0][email]']", data.email);

        // Sócios
        if (data.qsa?.length > 0) {
            setValue("[name='contatos[0][nome]']", data.qsa[0].nome_socio);
        }

    } catch (err) {
        alert("Erro: " + err.message);
    }
}


// --- AUTOMATIZAÇÃO ---
// roda a função automaticamente ao sair do campo CNPJ
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("cnpj").addEventListener("blur", buscarCNPJ);
});

function limpa_formulário_cep() {
    document.getElementById('endereco_logradouro').value = "";
    document.getElementById('endereco_bairro').value = "";
    document.getElementById('endereco_cidade').value = "";
    document.getElementById('endereco_estado').value = "";
}


async function pesquisacep(valor) {
    let cep = valor.replace(/\D/g, '');

    if (cep.length !== 8) {
        limpa_formulário_cep();
        alert("Formato de CEP inválido.");
        return;
    }

    try {
        let response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        let data = await response.json();

        if (data.erro) {
            limpa_formulário_cep();
            alert("CEP não encontrado.");
            return;
        }

        document.getElementById('endereco_logradouro').value = data.logradouro || "";
        document.getElementById('endereco_bairro').value = data.bairro || "";
        document.getElementById('endereco_cidade').value = data.localidade || "";
        document.getElementById('endereco_estado').value = data.uf || "";

    } catch (e) {
        limpa_formulário_cep();
        alert("Erro ao consultar o CEP.");
    }
}

function limpa_formulário_entrega_cep() {
    document.getElementById('entrega_logradouro').value = "";
    document.getElementById('entrega_bairro').value = "";
    document.getElementById('entrega_cidade').value = "";
    document.getElementById('entrega_estado').value = "";
}

async function pesquisacepentrega(valor) {
    let cep = valor.replace(/\D/g, '');

    if (cep.length !== 8) {
        limpa_formulário_entrega_cep();
        alert("Formato de CEP inválido.");
        return;
    }

    try {
        let response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        let data = await response.json();

        if (data.erro) {
            limpa_formulário_entrega_cep();
            alert("CEP não encontrado.");
            return;
        }

        document.getElementById('entrega_logradouro').value = data.logradouro || "";
        document.getElementById('entrega_bairro').value = data.bairro || "";
        document.getElementById('entrega_cidade').value = data.localidade || "";
        document.getElementById('entrega_estado').value = data.uf || "";

    } catch (e) {
        limpa_formulário_entrega_cep();
        alert("Erro ao consultar o CEP.");
    }
}

function mascara(t, mask) {
    var i = t.value.length;
    var saida = mask.substring(1, 0);
    var texto = mask.substring(i)
    if (texto.substring(0, 1) != saida) {
        t.value += texto.substring(0, 1);
    }
}

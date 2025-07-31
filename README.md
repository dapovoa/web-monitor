# Verificação de IPs

![Web Monitor](img/screenshot.png)

Sistema web para monitorização em tempo real do estado de ligação de dispositivos de rede através de ping.

## Funcionalidades

- Monitorização automática a cada 5 segundos
- Pings paralelos para maior velocidade (até 6 simultâneos)
- Categorização por tipo de dispositivo
- Interface responsiva e otimizada
- Estados visuais Online/Offline com indicadores coloridos
- **Proteção contra injeção de comandos**
- **Validação rigorosa de segurança de IPs**
- **Lista branca de IPs configurados**
- **Detecção inteligente de respostas de erro** (unreachable, timeout)

## Estrutura do Projeto

**Ficheiros principais:**
- `config.php` - Configuração de IPs organizados por categorias
- `index.php` - Interface principal do dashboard  
- `ping.php` - Engine de verificação de conectividade
- `README.md` - Documentação do projeto

**Pasta css:**
- `css\estilo.css` - Estilos responsivos da aplicação

**Pasta img:**
- `img\favicon.ico` - Ícone do site (ICO)
- `img\favicon.png` - Ícone do site (PNG)  
- `img\screenshot.png` - Captura de ecrã do dashboard

**Pasta js:**
- `js\script.js` - Lógica JavaScript para atualizações automáticas

## Requisitos

- Servidor Web (Apache/Nginx) com PHP 7.0+
- Sistema Windows para comando ping
- Extensões PHP: `exec()` e `proc_open()` habilitadas

## Segurança

### Proteção contra Injeção de Comandos
- Sanitização rigorosa de IPs (apenas números e pontos)
- Validação tripla: sanitização + configuração + filter_var()
- Comando ping fixo com escapeshellarg()
- Lista branca: apenas IPs do config.php são processados
- **Detecção de respostas falsas** (router proxy ARP, unreachable)

### Controlo de Acesso
- Verificação de referer (apenas chamadas do próprio domínio)
- Sem parâmetros externos aceites via GET/POST
- Impossível executar comandos via inspect/console
- Proteção contra bypass de validações
- Headers de segurança configurados

## Instalação

### Configurar Servidor Web
- Configurar Apache/Nginx para PHP
- Ativar extensões `exec()` e `proc_open()` 
- Configurar virtual host para HTTPS (recomendado)

### Configurar Aplicação
- Editar `config.php` com os IPs da sua rede
- Ajustar timeouts se necessário
- Verificar permissões de execução

## Configuração

Editar o ficheiro `config.php` para definir os IPs a monitorizar:

```php
<?php
$ipsGateways = [
    '192.168.1.1' => 'Gateway Principal',
    '192.168.2.1' => 'Gateway Secundário'
];

$ipsLojas = [
    '192.168.1.100' => 'PC Loja 1',
    '192.168.1.101' => 'PC Loja 2'
];
?>
```

### Categorias Disponíveis

- `$ipsGateways` - Gateways da rede
- `$ipsLojas` - Computadores das lojas
- `$ipsWifi` - Pontos de acesso WiFi
- `$ipsPC` - Computadores da sede e armazém
- `$ipsCCTV` - Sistemas de videovigilância

**Nota de Segurança**: Apenas os IPs definidos no config.php serão processados pelo sistema. Não é possível executar pings em IPs externos através de parâmetros ou manipulação do código.

## Informações Apresentadas

| Campo | Descrição |
|-------|-----------|
| ID | Número sequencial |
| IP | Endereço IP do dispositivo |
| LOCATION | Localização/Nome do dispositivo |
| CONNECTION | Estado da ligação (Online/Offline) |
| TTL | Time To Live da resposta |
| RTT (ms) | Tempo de resposta em milissegundos |

## Detecção Inteligente de Estado

O sistema detecta corretamente:
- ✅ **Ping bem-sucedido** - Resposta válida com RTT
- ❌ **Destination host unreachable** - Router responde mas IP não existe
- ❌ **Request timed out** - Sem resposta no tempo limite
- ❌ **Could not find host** - Erro de resolução DNS

## Performance

- **Pings paralelos**: Processa até 6 IPs simultaneamente
- **Timeout otimizado**: 1000ms por ping
- **Atualização inteligente**: Retry em 10s se erro, 5s se sucesso

## Tecnologias

- **Frontend**: HTML5, CSS3 responsivo, JavaScript ES6
- **Backend**: PHP com validações de segurança avançadas
- **Comando**: Windows ping (protegido contra injeção)
- **Atualização**: AJAX com fetch API
- **Segurança**: Sanitização, validação e lista branca de IPs
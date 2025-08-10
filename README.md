# Verificação de IPs

![Web Monitor](img/screenshot.png)

Sistema web para monitorização em tempo real do estado de dispositivos de rede através de ping.

## Funcionalidades

- Monitorização automática a cada 5 segundos
- Pings paralelos (até 6 simultâneos)
- Interface responsiva com estados Online/Offline
- Proteção contra injeção de comandos
- Categorização por tipo de dispositivo

## Requisitos

- Servidor Web com PHP 7.0+
- Sistema Windows
- Extensões `exec()` e `proc_open()` habilitadas

## Estrutura do Projeto

config.php - Configuração de IPs organizados por categorias
index.php - Interface principal do dashboard
ping.php - Engine de verificação de conectividade
README.md - Documentação do projeto
css/estilo.css - Estilos responsivos da aplicação
js/script.js - Lógica para atualizações automáticas
img/favicon.ico - Ícone do site (formato ICO)
img/favicon.png - Ícone do site (formato PNG)
img/screenshot.png - Captura de ecrã do dashboard

## Categorias Disponíveis

- $ipsGateways - Gateways da rede
- $ipsLojas - Computadores das lojas
- $ipsWifi - Pontos de acesso WiFi
- $ipsPC - Computadores da sede e armazém
- $ipsCCTV - Sistemas de videovigilância

## Segurança

- Sanitização rigorosa de endereços IP
- Lista branca de IPs configurados
- Proteção contra injeção de comandos
- Validação tripla com filter_var()
- Verificação de referer HTTP

## Tecnologias

- Frontend: HTML5, CSS3, JavaScript ES6
- Backend: PHP 7.0+
- Comando: Windows ping protegido
- Atualização: AJAX com fetch API
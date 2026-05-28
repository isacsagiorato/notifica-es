use eswdev14_dsw_2026;

create table login (
    id int(11) AUTO_INCREMENT primary key ,
    data_cadastro datetime not null default current_timestamp,
    tipo_usuario enum('administrador', 'ong', 'rastreador', 'adotante', 'veterinario') not null,
    email varchar(255) not null,
    senha varchar(255) not null,
    status enum('a', 'i') not null default 'a',
    fk_adotante_id int(11) null,
    fk_rastreador_id int(11) null,
    fk_ong_id int(11) null,
    fk_administrador_id int(11) null,
    fk_veterinario_id int(11) null,
    unique key login_email_unique (email)
);

create table administrador (
    id int(11) AUTO_INCREMENT primary key ,
    nome varchar(150),
    cpf varchar(14),
    cep varchar(9),
    logradouro varchar(100),
    estado varchar(100),
    complemento text,
    data_nascimento date,
    cidade varchar(100),
    bairro varchar(100),
    telefone_1 varchar(20),
    telefone_2 varchar(20)
);

create table veterinario (
    id int(11) AUTO_INCREMENT primary key ,
    nome varchar(100),
    crvm varchar(8),
    data_nascimento date,
    cpf varchar(14),
    telefone varchar(20),
    cep varchar(9),
    logradouro varchar(150),
    cidade varchar(100),
    telefone_2 varchar(20),
    numero int(11),
    bairro varchar(100),
    complemento text,
    estado varchar(100)
);

create table adotante (
    id int(11) AUTO_INCREMENT primary key ,
    nome varchar(100),
    cpf varchar(14),
    status enum('pessimo', 'regular', 'bom', 'muito bom', 'excelente'),
    data_nascimento date,
    cep varchar(9),
    numero int(11),
    bairro varchar(100),
    cidade varchar(100),
    estado varchar(100),
    complemento text,
    telefone_1 varchar(20),
    telefone_2 varchar(20),
    logradouro varchar(150)
);

create table ong (
    id int(11) AUTO_INCREMENT primary key ,
    cep varchar(9),
    cnpj varchar(18),
    status enum('a','i'),
    quantidade_animais int(11),
    nome varchar(150),
    telefone_1 varchar(20),
    telefone_2 varchar(20),
    bairro varchar(100),
    estado varchar(100),
    complemento text,
    logradouro varchar(100),
    cidade varchar(100)
);

create table rastreador (
    id int(11) AUTO_INCREMENT primary key ,
    nome varchar(100),
    cpf varchar(14),
    cep varchar(9),
    logradouro varchar(100),
    bairro varchar(100),
    cidade varchar(100),
    estado varchar(100),
    telefone_1 varchar(20),
    telefone_2 varchar(20),
    complemento text
);

create table clinica (
    id int(11) AUTO_INCREMENT primary key ,
    nome varchar(100),
    cnpj varchar(18),
    cep varchar(9),
    telefone_1 varchar(20),
    logradouro varchar(150),
    numero int(11),
    bairro varchar(100),
    cidade varchar(100),
    estado varchar(100),
    complemento text,
    telefone_2 varchar(20)
);

create table vet_clinica (
    id int(11) AUTO_INCREMENT primary key ,
    fk_clinica_id int(11),
    fk_veterinario_id int(11)
);

create table historico_animal (
    id int(11) AUTO_INCREMENT primary key ,
    descricao text,
    data datetime,
    tipo enum('vacinacao', 'procedimento', 'ocorrencia'),
    fk_animal_id int(11),
    fk_ong_id int(11),
    fk_veterinario_id int(11)
);

create table animal (
    id int(11) AUTO_INCREMENT primary key ,
    nome varchar(100),
    data_nascimento date,
    sexo enum('m', 'f')
);

create table animal_raca (
    id int(11) AUTO_INCREMENT primary key ,
    fk_raca_id int(11),
    fk_animal_id int(11)
);

create table raca (
    id int(11) AUTO_INCREMENT primary key ,
    nome varchar(100),
    fk_especie_id int(11)
);

create table especie (
    id int(11) AUTO_INCREMENT primary key ,
    nome varchar(100)
);

create table solicitacao_adocao (
    id int(11) AUTO_INCREMENT primary key ,
    data datetime,
    status enum('a', 'i', 'p'),
    motivo text,
    fk_adotante_id int(11),
    fk_animal_id int(11)
);

create table ong_animal (
    id int(11) AUTO_INCREMENT primary key ,
    fk_ong_id int(11),
    fk_animal_id int(11)
);
 
alter table login add constraint fk_login_2
    foreign key (fk_adotante_id)
    references adotante (id)
    on delete cascade;
 
alter table login add constraint fk_login_3
    foreign key (fk_rastreador_id)
    references rastreador (id)
    on delete cascade;
 
alter table login add constraint fk_login_4
    foreign key (fk_ong_id)
    references ong (id)
    on delete cascade;
 
alter table login add constraint fk_login_5
    foreign key (fk_administrador_id)
    references administrador (id)
    on delete cascade;
 
alter table login add constraint fk_login_6
    foreign key (fk_veterinario_id)
    references veterinario (id)
    on delete cascade;
 
alter table vet_clinica add constraint fk_vet_clinica_2
    foreign key (fk_clinica_id)
    references clinica (id)
    on delete cascade;
 
alter table vet_clinica add constraint fk_vet_clinica_3
    foreign key (fk_veterinario_id)
    references veterinario (id)
    on delete cascade;
 
alter table historico_animal add constraint fk_historico_animal_2
    foreign key (fk_animal_id)
    references animal (id)
    on delete cascade;
 
alter table historico_animal add constraint fk_historico_animal_3
    foreign key (fk_ong_id)
    references ong (id)
    on delete cascade;
 
alter table historico_animal add constraint fk_historico_animal_4
    foreign key (fk_veterinario_id)
    references veterinario (id)
    on delete cascade;
 
alter table animal_raca add constraint fk_animal_raca_2
    foreign key (fk_raca_id)
    references raca (id)
    on delete cascade;
 
alter table animal_raca add constraint fk_animal_raca_3
    foreign key (fk_animal_id)
    references animal (id)
    on delete restrict;
 
alter table raca add constraint fk_raca_2
    foreign key (fk_especie_id)
    references especie (id)
    on delete cascade;
 
alter table solicitacao_adocao add constraint fk_solicitacao_adocao_2
    foreign key (fk_adotante_id)
    references adotante (id)
    on delete cascade;
 
alter table solicitacao_adocao add constraint fk_solicitacao_adocao_3
    foreign key (fk_animal_id)
    references animal (id)
    on delete cascade;
 
alter table ong_animal add constraint fk_ong_animal_2
    foreign key (fk_ong_id)
    references ong (id)
    on delete cascade;
 
alter table ong_animal add constraint fk_ong_animal_3
    foreign key (fk_animal_id)
    references animal (id)
    on delete cascade;
    

-- adição do numero nas tabelas faltantes.
alter table ong add numero int(11);
alter table administrador add numero int(11);
alter table rastreador add numero int(11);

-- Alteração de nome errado na tabela do veterinario
alter table veterinario change crvm crmv VARCHAR(8);

-- Alteração da tabela Adotante, adicionando a Fk do Login
Alter table adotante 
add fk_login_id int(11) NOT NULL;
Alter table adotante 
add constraint fk_adotante_login
 foreign key(fk_login_id)
 references login(id);

alter table animal 
    add column especie varchar(50) null,
    add column porte enum('pequeno', 'medio', 'grande') null,
    add column localizacao varchar(100) null,
    add column foto varchar(255) null,
    add column status enum('disponivel', 'adotado', 'em_tratamento', 'reservado') not null default 'disponivel';


CREATE TABLE Commodities
(
    id int,
    title string,
    server_id int
);
CREATE UNIQUE INDEX Commodity_id_uindex ON Commodity (id);

create table Commodities_Data
(
    commodity_id int,
    station_id   int,
    faction_id   int,
    store_id     int,
    server_id    int,
    amount       double,
    price        double,
    created_at   text,
    updated_at   text
);

CREATE TABLE Stations
(
    id int,
    faction_id int,
    station_type int,
    title string
);
CREATE UNIQUE INDEX Station_id_uindex ON Station (id);

CREATE TABLE Factions
(
    id int,
    title varchar
);
CREATE UNIQUE INDEX Faction_id_uindex ON Faction (id);

CREATE TABLE Stores
(
    id int,
    faction_id int,
    station_id int
);
CREATE UNIQUE INDEX Stores_id_uindex ON Stores (id);

CREATE TABLE Commodities_Amount
(
    commodity_id int,
    server_id int,
    station_id int,
    faction_id int,
    store_id int,
    created_at text,
    amount double
);

create table Factions_Stations
(
  faction_id int,
  station_id int
);
create unique index Factions_Stations_station_id_uindex on Factions_Stations (station_id);

CREATE TABLE Types
(
    id int,
    title text
);
CREATE UNIQUE INDEX types_id_uindex ON Types (id);

create table Users
(
  id         int,
  first_name text not null,
  last_name  text not null,
  username   text not null
);

create unique index Users_id_uindex
  on Users (id);

CREATE TABLE Servers
(
    id int NOT NULL,
    title int
);

create table Servers_Users
(
  station_id int not null,
  user_id    int not null
);

CREATE TABLE Servers_Stations
(
    server_id int NOT NULL,
    station_id int NOT NULL
);

create table Permissions
(
  id   int,
  title int
);

create table Permissions_Users
(
  user_id        int not null,
  permissions_id int not null
);



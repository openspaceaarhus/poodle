begin transaction;

create language 'plpgsql';
CREATE OR REPLACE FUNCTION update_updated()
	RETURNS TRIGGER AS $$
	BEGIN
	   NEW.updated = now(); 
	   RETURN NEW;
	END;
$$ language 'plpgsql';

create table pizza_place (
       id serial primary key,	
       created timestamp default now(),
       updated timestamp default now(),
       
       name varchar(100),
       logo_img varchar(100),
       url varchar(100),				
       catalog_url varchar(100),
       phone_1 integer,
       phone_2 integer
);


CREATE TRIGGER update_updated BEFORE UPDATE
        ON pizza_place FOR EACH ROW EXECUTE PROCEDURE 
        update_updated();

insert into pizza_place(name, logo_img, url, catalog_url, phone_1, phone_2)
values ('Liberta', NULL, 'http://www.vericenter.dk/Butikker/Pizzeria_Liberta.aspx', 'http://www.just-eat.dk/restaurants-liberta/menu-2201', 86217898, 86217888);

create table pizza_order (
       id serial primary key,	
       created timestamp default now(),
       updated timestamp default now(),
       pizza_order text default 'hack mad',

       pizza_place integer references pizza_place(id),
       admin_uuid varchar(42) unique ,
       user_uuid varchar(42) unique ,
       
       driver varchar(100),
       collector varchar(100),
       
       pickup_time timestamp default NULL,
       order_time timestamp default NULL
);


CREATE TRIGGER update_updated BEFORE UPDATE
        ON pizza_order FOR EACH ROW EXECUTE PROCEDURE 
        update_updated();

create table pizza (
       id serial primary key,	-- pizza id
       created timestamp default now(),
       updated timestamp default now(),
       order_id integer references pizza_order(id),
       
       username  varchar(150),	 
       pizza_id  varchar(50),
       comment  varchar(1024),	
       price numeric(5,2) default 60, 	--max prize 999.99

       chili boolean,
       garlic boolean,
       cheese boolean,
       bacon boolean,

       paid boolean
);


CREATE TRIGGER update_updated BEFORE UPDATE
        ON pizza FOR EACH ROW EXECUTE PROCEDURE 
        update_updated();
commit;

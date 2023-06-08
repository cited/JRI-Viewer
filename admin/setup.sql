CREATE TYPE public.level AS ENUM ('User', 'Admin');
CREATE TYPE public.userlevel AS ENUM ('Admin','User');

CREATE TABLE public.access_groups (
	id integer NOT NULL,
	name character varying(255) NOT NULL
);

CREATE SEQUENCE public.access_groups_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.access_groups_id_seq OWNED BY public.access_groups.id;

CREATE SEQUENCE public.user_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE public.basemaps (
    id integer DEFAULT nextval('public.user_seq'::regclass) NOT NULL,
    basemap_name character varying(255),
    basemap_url character varying(255)
);

CREATE TABLE public.group_access (
    id integer NOT NULL,
    access_group_id integer NOT NULL,
    report_group_id integer NOT NULL
);

CREATE SEQUENCE public.group_access_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.group_access_id_seq OWNED BY public.group_access.id;

CREATE TABLE public.groups (
    id integer NOT NULL,
    name character varying(255),
    reportids character varying(255),
    owner numeric,
    description character varying(255)
);

CREATE SEQUENCE public.groups_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.groups_id_seq OWNED BY public.groups.id;

CREATE TABLE public.inputs (
    id integer DEFAULT nextval('public.user_seq'::regclass) NOT NULL,
    input character varying(2550),
    name character varying(255),
    report_id numeric
);

CREATE TABLE public.jasper (
    id integer NOT NULL,
    url character varying(255),
    repname character varying(200),
    datasource character varying(200),
    download_only character varying(200),
    outname character varying(200),
    name character varying(200),
    owner numeric(10,0),
    is_grouped numeric(10,0) DEFAULT 0,
    description character varying(255)
);

CREATE SEQUENCE public.jesper_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jesper_id_seq OWNED BY public.jasper.id;


CREATE TABLE public.parameters (
    id integer DEFAULT nextval('public.user_seq'::regclass) NOT NULL,
    reportid numeric,
    ptype character varying(250),
    pvalues character varying(250),
    pname character varying(250)
);


CREATE TABLE public.report_access (
    id integer NOT NULL,
    access_group_id integer NOT NULL,
    report_id integer NOT NULL
);

CREATE SEQUENCE public.report_access_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.report_access_id_seq OWNED BY public.report_access.id;


CREATE TABLE public.user (
    id integer NOT NULL,
    name character varying(250),
    email character varying(250),
    password character varying(250),
    accesslevel character varying
);

CREATE TABLE public.user_access (
    id integer NOT NULL,
    user_id integer NOT NULL,
    access_group_id integer NOT NULL
);

CREATE SEQUENCE public.user_access_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.user_access_id_seq OWNED BY public.user_access.id;

CREATE SEQUENCE public.user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.user_id_seq OWNED BY public.user.id;

CREATE TABLE public.links (
    id integer NOT NULL,
    url character varying(250)
);

CREATE TABLE public.link_access (
    id integer NOT NULL,
    link_id integer NOT NULL,
    access_group_id integer NOT NULL
);

CREATE SEQUENCE public.link_access_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.link_access_id_seq OWNED BY public.link_access.id;

CREATE SEQUENCE public.link_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER TABLE ONLY public.access_groups ALTER COLUMN id SET DEFAULT nextval('public.access_groups_id_seq'::regclass);
ALTER TABLE ONLY public.group_access ALTER COLUMN id SET DEFAULT nextval('public.group_access_id_seq'::regclass);
ALTER TABLE ONLY public.groups ALTER COLUMN id SET DEFAULT nextval('public.groups_id_seq'::regclass);
ALTER TABLE ONLY public.jasper ALTER COLUMN id SET DEFAULT nextval('public.jesper_id_seq'::regclass);
ALTER TABLE ONLY public.report_access ALTER COLUMN id SET DEFAULT nextval('public.report_access_id_seq'::regclass);
ALTER TABLE ONLY public.user ALTER COLUMN id SET DEFAULT nextval('public.user_id_seq'::regclass);
ALTER TABLE ONLY public.user_access ALTER COLUMN id SET DEFAULT nextval('public.user_access_id_seq'::regclass);

ALTER TABLE ONLY public.links ALTER COLUMN id SET DEFAULT nextval('public.link_id_seq'::regclass);
ALTER TABLE ONLY public.link_access ALTER COLUMN id SET DEFAULT nextval('public.link_access_id_seq'::regclass);

-- password is 1234
INSERT INTO public.user VALUES
	(1, 'John Smith', 'admin@admin.com', '81dc9bdb52d04dc20036dbd8313ed055', 'Admin');

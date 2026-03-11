--
-- PostgreSQL database dump
--

-- Dumped from database version 14.15 (Ubuntu 14.15-0ubuntu0.22.04.1)
-- Dumped by pg_dump version 14.15 (Ubuntu 14.15-0ubuntu0.22.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: absent_devices; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.absent_devices (
    id bigint NOT NULL,
    absent_device_id character varying(255) NOT NULL,
    publish_topic character varying(255) NOT NULL,
    subscribe_topic character varying(255) NOT NULL,
    branch character varying(255) NOT NULL,
    building character varying(255) NOT NULL,
    room character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: absent_devices_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.absent_devices_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: absent_devices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.absent_devices_id_seq OWNED BY public.absent_devices.id;


--
-- Name: absent_last_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.absent_last_logs (
    id bigint NOT NULL,
    absent_device_id bigint NOT NULL,
    absent_log_id bigint NOT NULL,
    value character varying(255) NOT NULL,
    status character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: absent_last_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.absent_last_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: absent_last_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.absent_last_logs_id_seq OWNED BY public.absent_last_logs.id;


--
-- Name: absent_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.absent_logs (
    id bigint NOT NULL,
    absent_device_id bigint NOT NULL,
    value character varying(255) NOT NULL,
    status character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: absent_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.absent_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: absent_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.absent_logs_id_seq OWNED BY public.absent_logs.id;


--
-- Name: absent_received_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.absent_received_logs (
    id bigint NOT NULL,
    absent_device_id bigint NOT NULL,
    absent_log_id bigint NOT NULL,
    marked_as_read boolean DEFAULT false NOT NULL,
    value character varying(255) NOT NULL,
    status character varying(255) NOT NULL,
    notes character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: absent_received_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.absent_received_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: absent_received_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.absent_received_logs_id_seq OWNED BY public.absent_received_logs.id;


--
-- Name: cam_payloads; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cam_payloads (
    id bigint NOT NULL,
    device_log_id bigint NOT NULL,
    file_name character varying(255) NOT NULL,
    file text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    latlong character varying(255)
);


--
-- Name: COLUMN cam_payloads.latlong; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.cam_payloads.latlong IS 'Latitude and Longitude of the image';


--
-- Name: cam_payloads_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cam_payloads_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cam_payloads_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cam_payloads_id_seq OWNED BY public.cam_payloads.id;


--
-- Name: device_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.device_logs (
    id bigint NOT NULL,
    device_id bigint,
    value character varying(255) NOT NULL,
    type character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: device_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.device_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: device_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.device_logs_id_seq OWNED BY public.device_logs.id;


--
-- Name: device_status; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.device_status (
    id bigint NOT NULL,
    marked_as_read boolean DEFAULT false NOT NULL,
    notes character varying(255),
    device_id bigint,
    status_type_id bigint,
    device_log_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    user_id bigint,
    noted boolean DEFAULT false NOT NULL,
    is_normal_state boolean DEFAULT false NOT NULL
);


--
-- Name: device_status_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.device_status_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: device_status_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.device_status_id_seq OWNED BY public.device_status.id;


--
-- Name: device_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.device_types (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: device_types_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.device_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: device_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.device_types_id_seq OWNED BY public.device_types.id;


--
-- Name: devices; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.devices (
    id bigint NOT NULL,
    device_id character varying(255) NOT NULL,
    device_type_id bigint,
    publish_topic character varying(255) NOT NULL,
    subscribe_topic character varying(255) NOT NULL,
    branch character varying(255) NOT NULL,
    building character varying(255) NOT NULL,
    room character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    cam_topic character varying(255),
    is_online boolean DEFAULT true NOT NULL,
    last_ping_at timestamp(0) without time zone,
    active_hour character varying(255),
    inactive_hour character varying(255)
);


--
-- Name: COLUMN devices.active_hour; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.devices.active_hour IS 'h:m || hh:mm';


--
-- Name: COLUMN devices.inactive_hour; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.devices.inactive_hour IS 'h:m || hh:mm';


--
-- Name: devices_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.devices_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: devices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.devices_id_seq OWNED BY public.devices.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


--
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


--
-- Name: notifs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notifs (
    id bigint NOT NULL,
    absent_device_id bigint,
    device_id bigint,
    notif_type character varying(255) NOT NULL,
    notif_status character varying(255) NOT NULL,
    message character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT notifs_notif_status_check CHECK (((notif_status)::text = ANY (ARRAY[('unread'::character varying)::text, ('read'::character varying)::text]))),
    CONSTRAINT notifs_notif_type_check CHECK (((notif_type)::text = ANY (ARRAY[('absent_device'::character varying)::text, ('dynamic_device'::character varying)::text])))
);


--
-- Name: notifs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.notifs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: notifs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.notifs_id_seq OWNED BY public.notifs.id;


--
-- Name: password_resets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_resets (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: publish_actions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.publish_actions (
    id bigint NOT NULL,
    device_id bigint,
    label character varying(255) NOT NULL,
    value character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: publish_actions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.publish_actions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: publish_actions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.publish_actions_id_seq OWNED BY public.publish_actions.id;


--
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.settings (
    id bigint NOT NULL,
    app_name character varying(255) NOT NULL,
    logo text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    is_access_device boolean DEFAULT false NOT NULL,
    mqtt_main_topic character varying(255) DEFAULT 'mcc'::character varying NOT NULL,
    email_users json,
    chat_id_telegram character varying(255),
    location_widget boolean DEFAULT true NOT NULL
);


--
-- Name: settings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.settings_id_seq OWNED BY public.settings.id;


--
-- Name: status_type_widgets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.status_type_widgets (
    id bigint NOT NULL,
    setting_id bigint,
    status_type_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: status_type_widgets_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.status_type_widgets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: status_type_widgets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.status_type_widgets_id_seq OWNED BY public.status_type_widgets.id;


--
-- Name: status_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.status_types (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    color character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    trigger_color character varying(255)
);


--
-- Name: status_types_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.status_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: status_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.status_types_id_seq OWNED BY public.status_types.id;


--
-- Name: subscribe_expressions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.subscribe_expressions (
    id bigint NOT NULL,
    device_id bigint,
    status_type_id bigint,
    expression character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    normal_state boolean DEFAULT false NOT NULL
);


--
-- Name: subscribe_expressions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.subscribe_expressions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: subscribe_expressions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.subscribe_expressions_id_seq OWNED BY public.subscribe_expressions.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    user_code character varying(255),
    job_position character varying(255),
    work_area character varying(255),
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    absent_device_id bigint
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: absent_devices id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.absent_devices ALTER COLUMN id SET DEFAULT nextval('public.absent_devices_id_seq'::regclass);


--
-- Name: absent_last_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.absent_last_logs ALTER COLUMN id SET DEFAULT nextval('public.absent_last_logs_id_seq'::regclass);


--
-- Name: absent_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.absent_logs ALTER COLUMN id SET DEFAULT nextval('public.absent_logs_id_seq'::regclass);


--
-- Name: absent_received_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.absent_received_logs ALTER COLUMN id SET DEFAULT nextval('public.absent_received_logs_id_seq'::regclass);


--
-- Name: cam_payloads id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cam_payloads ALTER COLUMN id SET DEFAULT nextval('public.cam_payloads_id_seq'::regclass);


--
-- Name: device_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.device_logs ALTER COLUMN id SET DEFAULT nextval('public.device_logs_id_seq'::regclass);


--
-- Name: device_status id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.device_status ALTER COLUMN id SET DEFAULT nextval('public.device_status_id_seq'::regclass);


--
-- Name: device_types id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.device_types ALTER COLUMN id SET DEFAULT nextval('public.device_types_id_seq'::regclass);


--
-- Name: devices id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.devices ALTER COLUMN id SET DEFAULT nextval('public.devices_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: notifs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifs ALTER COLUMN id SET DEFAULT nextval('public.notifs_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: publish_actions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publish_actions ALTER COLUMN id SET DEFAULT nextval('public.publish_actions_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: settings id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.settings ALTER COLUMN id SET DEFAULT nextval('public.settings_id_seq'::regclass);


--
-- Name: status_type_widgets id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.status_type_widgets ALTER COLUMN id SET DEFAULT nextval('public.status_type_widgets_id_seq'::regclass);


--
-- Name: status_types id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.status_types ALTER COLUMN id SET DEFAULT nextval('public.status_types_id_seq'::regclass);


--
-- Name: subscribe_expressions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subscribe_expressions ALTER COLUMN id SET DEFAULT nextval('public.subscribe_expressions_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: absent_devices absent_devices_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.absent_devices
    ADD CONSTRAINT absent_devices_pkey PRIMARY KEY (id);


--
-- Name: absent_last_logs absent_last_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.absent_last_logs
    ADD CONSTRAINT absent_last_logs_pkey PRIMARY KEY (id);


--
-- Name: absent_logs absent_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.absent_logs
    ADD CONSTRAINT absent_logs_pkey PRIMARY KEY (id);


--
-- Name: absent_received_logs absent_received_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.absent_received_logs
    ADD CONSTRAINT absent_received_logs_pkey PRIMARY KEY (id);


--
-- Name: cam_payloads cam_payloads_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cam_payloads
    ADD CONSTRAINT cam_payloads_pkey PRIMARY KEY (id);


--
-- Name: device_logs device_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.device_logs
    ADD CONSTRAINT device_logs_pkey PRIMARY KEY (id);


--
-- Name: device_status device_status_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.device_status
    ADD CONSTRAINT device_status_pkey PRIMARY KEY (id);


--
-- Name: device_types device_types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.device_types
    ADD CONSTRAINT device_types_pkey PRIMARY KEY (id);


--
-- Name: devices devices_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.devices
    ADD CONSTRAINT devices_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- Name: notifs notifs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifs
    ADD CONSTRAINT notifs_pkey PRIMARY KEY (id);


--
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: publish_actions publish_actions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publish_actions
    ADD CONSTRAINT publish_actions_pkey PRIMARY KEY (id);


--
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id);


--
-- Name: status_type_widgets status_type_widgets_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.status_type_widgets
    ADD CONSTRAINT status_type_widgets_pkey PRIMARY KEY (id);


--
-- Name: status_types status_types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.status_types
    ADD CONSTRAINT status_types_pkey PRIMARY KEY (id);


--
-- Name: subscribe_expressions subscribe_expressions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subscribe_expressions
    ADD CONSTRAINT subscribe_expressions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: fk_device_logs_to_devices; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fk_device_logs_to_devices ON public.device_logs USING btree (device_id);


--
-- Name: fk_device_status_to_device_logs; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fk_device_status_to_device_logs ON public.device_status USING btree (device_log_id);


--
-- Name: fk_device_status_to_devices; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fk_device_status_to_devices ON public.device_status USING btree (device_id);


--
-- Name: fk_device_status_to_status_types; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fk_device_status_to_status_types ON public.device_status USING btree (status_type_id);


--
-- Name: fk_device_status_to_users; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fk_device_status_to_users ON public.device_status USING btree (user_id);


--
-- Name: fk_devices_to_device_types; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fk_devices_to_device_types ON public.devices USING btree (device_type_id);


--
-- Name: fk_publish_actions_to_devices; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fk_publish_actions_to_devices ON public.publish_actions USING btree (device_id);


--
-- Name: fk_status_type_widgets_to_settings; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fk_status_type_widgets_to_settings ON public.status_type_widgets USING btree (setting_id);


--
-- Name: fk_status_type_widgets_to_status_types; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fk_status_type_widgets_to_status_types ON public.status_type_widgets USING btree (status_type_id);


--
-- Name: fk_subscribe_expressions_to_devices; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fk_subscribe_expressions_to_devices ON public.subscribe_expressions USING btree (device_id);


--
-- Name: fk_subscribe_expressions_to_status_types; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fk_subscribe_expressions_to_status_types ON public.subscribe_expressions USING btree (status_type_id);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- Name: password_resets_email_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX password_resets_email_index ON public.password_resets USING btree (email);


--
-- Name: absent_last_logs absent_last_logs_absent_device_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.absent_last_logs
    ADD CONSTRAINT absent_last_logs_absent_device_id_foreign FOREIGN KEY (absent_device_id) REFERENCES public.absent_devices(id) ON DELETE CASCADE;


--
-- Name: absent_last_logs absent_last_logs_absent_log_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.absent_last_logs
    ADD CONSTRAINT absent_last_logs_absent_log_id_foreign FOREIGN KEY (absent_log_id) REFERENCES public.absent_logs(id) ON DELETE CASCADE;


--
-- Name: absent_logs absent_logs_absent_device_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.absent_logs
    ADD CONSTRAINT absent_logs_absent_device_id_foreign FOREIGN KEY (absent_device_id) REFERENCES public.absent_devices(id) ON DELETE CASCADE;


--
-- Name: absent_received_logs absent_received_logs_absent_device_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.absent_received_logs
    ADD CONSTRAINT absent_received_logs_absent_device_id_foreign FOREIGN KEY (absent_device_id) REFERENCES public.absent_devices(id) ON DELETE CASCADE;


--
-- Name: absent_received_logs absent_received_logs_absent_log_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.absent_received_logs
    ADD CONSTRAINT absent_received_logs_absent_log_id_foreign FOREIGN KEY (absent_log_id) REFERENCES public.absent_logs(id) ON DELETE CASCADE;


--
-- Name: device_logs fk_device_logs_to_devices; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.device_logs
    ADD CONSTRAINT fk_device_logs_to_devices FOREIGN KEY (device_id) REFERENCES public.devices(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: device_status fk_device_status_to_device_logs; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.device_status
    ADD CONSTRAINT fk_device_status_to_device_logs FOREIGN KEY (device_log_id) REFERENCES public.device_logs(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: device_status fk_device_status_to_devices; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.device_status
    ADD CONSTRAINT fk_device_status_to_devices FOREIGN KEY (device_id) REFERENCES public.devices(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: device_status fk_device_status_to_status_types; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.device_status
    ADD CONSTRAINT fk_device_status_to_status_types FOREIGN KEY (status_type_id) REFERENCES public.status_types(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: device_status fk_device_status_to_users; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.device_status
    ADD CONSTRAINT fk_device_status_to_users FOREIGN KEY (user_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: devices fk_devices_to_device_types; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.devices
    ADD CONSTRAINT fk_devices_to_device_types FOREIGN KEY (device_type_id) REFERENCES public.device_types(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: publish_actions fk_publish_actions_to_devices; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publish_actions
    ADD CONSTRAINT fk_publish_actions_to_devices FOREIGN KEY (device_id) REFERENCES public.devices(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: status_type_widgets fk_status_type_widgets_to_settings; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.status_type_widgets
    ADD CONSTRAINT fk_status_type_widgets_to_settings FOREIGN KEY (setting_id) REFERENCES public.settings(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: status_type_widgets fk_status_type_widgets_to_status_types; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.status_type_widgets
    ADD CONSTRAINT fk_status_type_widgets_to_status_types FOREIGN KEY (status_type_id) REFERENCES public.status_types(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: subscribe_expressions fk_subscribe_expressions_to_devices; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subscribe_expressions
    ADD CONSTRAINT fk_subscribe_expressions_to_devices FOREIGN KEY (device_id) REFERENCES public.devices(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: subscribe_expressions fk_subscribe_expressions_to_status_types; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subscribe_expressions
    ADD CONSTRAINT fk_subscribe_expressions_to_status_types FOREIGN KEY (status_type_id) REFERENCES public.status_types(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: notifs notifs_absent_device_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifs
    ADD CONSTRAINT notifs_absent_device_id_foreign FOREIGN KEY (absent_device_id) REFERENCES public.absent_devices(id) ON DELETE CASCADE;


--
-- Name: notifs notifs_device_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifs
    ADD CONSTRAINT notifs_device_id_foreign FOREIGN KEY (device_id) REFERENCES public.devices(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: users users_absent_device_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_absent_device_id_foreign FOREIGN KEY (absent_device_id) REFERENCES public.absent_devices(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--


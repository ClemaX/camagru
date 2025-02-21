#!/bin/sh

POSTGRES_DB="${POSTGRES_DB:-database}"

PGADMIN_SERVER_JSON_FILE=/var/lib/pgadmin/servers.json

cat > "$PGADMIN_SERVER_JSON_FILE" << EOF
{
    "Servers": {
        "1": {
            "Name": "$POSTGRES_DB",
            "Group": "Servers",
            "Host": "$DATABASE_HOST",
            "Port": $DATABASE_PORT,
            "MaintenanceDB": "postgres",
            "Username": "$POSTGRES_USER",
            "UseSSHTunnel": 0,
            "TunnelPort": "22",
            "TunnelAuthentication": 0,
            "KerberosAuthentication": false,
            "ConnectionParameters": {
                "sslmode": "prefer",
                "connect_timeout": 10,
                "sslcert": "<STORAGE_DIR>/.postgresql/postgresql.crt",
                "sslkey": "<STORAGE_DIR>/.postgresql/postgresql.key"
            }
        }
    }
}
EOF

export PGADMIN_SERVER_JSON_FILE

exec /entrypoint.sh "$@"

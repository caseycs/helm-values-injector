# Inject secrets from Vault to Helm charts

Templating for Helm charts to store secrets in Vault.
Allows retrieve secrets on deployment.

## Use-cases

### Helm values files

```yaml
helmChartKey: serviceName
rootPassword: ##vaultPath.secret.key##
```

### Files with raw secret content

`secretFile.vault`

```yaml
vaultPath.secret.key
```

`secretFile` will be written with raw content from Vault.

Binary files can be stored in Vault base64-encoded, to be decoded on placement:

`secretFile.vault.base64`

```yaml
vaultPath.secret.key
```
## Usage

```
export VAULT_TOKEN=myroot
export VAULT_URL=http://docker-machine:8200
php helm-values-injector.php dir1 [dir2] [file3] [...]
```

## Testing

Run vault

```shell script
export VAULT_TOKEN=myroot
echo "ui = true" > config.hcl
docker run --rm \
  -e "VAULT_DEV_ROOT_TOKEN_ID=$VAULT_TOKEN" \
  -p 8200:8200 \
  vault
```

Populate some data

```shell script
export VAULT_TOKEN=myroot
curl -X POST -H "Content-Type: application/json" -H "X-Vault-Token:$VAULT_TOKEN" \
  --data '{"data":{"bla":"secretbla"}}' \
  http://docker-machine:8200/v1/secret/data/test1
curl -X POST -H "Content-Type: application/json" -H "X-Vault-Token:$VAULT_TOKEN" \
  --data '{"data":{"bla":"secretbla"}}' \
  http://docker-machine:8200/v1/secret/data/test2
```

Run application and compare test directory content

```shell script
php helm-values-injector.php test
git diff
```

## Todo

Rewrite in golang to provide single binary =)
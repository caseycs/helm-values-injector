# Inject secrets from Vault to Helm values

To be able to store Helm charts in git without secrets

## Testing

Run vault

```shell script
export VAULT_TOKEN=myroot
echo "ui = true" > config.hcl
docker run --rm --cap-add=IPC_LOCK  --name=dev-vault -e "VAULT_DEV_ROOT_TOKEN_ID=$VAULT_TOKEN" -p 8200:8200 vault
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
./helm-values-injector.php
git diff
```
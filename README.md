# Process Manager
Development version

#### Some commands
```shell script
./bin/console messenger:setup-transports
./bin/console messenger:consume debug_transport --env=local

./bin/console app:dev:try
```


#### Make dev environment
```shell script
# To build image
make build

# To start image
make up

# To go to application console
make console

# To clean up
make clean 
```

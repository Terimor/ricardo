#!/bin/bash
ssh -i ~/.ssh/ODIN_Prod.pem ubuntu@52.3.54.183 'cd deploy && dep rollback'

space="nmmm-test"
REGION="ams3"
STORAGETYPE="STANDARD" # Storage type, can be STANDARD, REDUCED_REDUNDANCY, etc.
KEY="xxx"
SECRET="xxx"


path="/home/nmmm/"
file="despicable-me-2-minions_a-G-10438535-0.jpg"
space_path="/"
date=$(date +"%a, %d %b %Y %T %z")
acl="x-amz-acl:public-read" # private or public-read.
content_type="image/jpeg"
storage_type="x-amz-storage-class:${STORAGETYPE}"



string="PUT\n\n$content_type\n$date\n$acl\n$storage_type\n/$space$space_path$file"
signature=$(echo -en "${string}" | openssl sha1 -hmac "${SECRET}" -binary | base64)
curl -v -s -X PUT -T "$path/$file" \
-H "Host: $space.${REGION}.digitaloceanspaces.com" \
-H "Date: $date" \
-H "Content-Type: $content_type" \
-H "$storage_type" \
-H "$acl" \
-H "Authorization: AWS ${KEY}:$signature" \
"https://$space.${REGION}.digitaloceanspaces.com$space_path$file"



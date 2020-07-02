FROM alpine

MAINTAINER Grégory Planchat <gregory@kiboko.fr>

RUN set -ex \
    && apk add \
        py3-pip \
    && pip install --upgrade pip \
    && pip install komodo-python3-dbgp

CMD /usr/bin/pydbgpproxy -d 0.0.0.0:9000 -i 0.0.0.0:9001

EXPOSE 9000 9001
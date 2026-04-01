FROM node:20-alpine

WORKDIR /app

# Expor a porta que o Next.js usa
EXPOSE 3000

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

CMD ["/usr/local/bin/entrypoint.sh"]

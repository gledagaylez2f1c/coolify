const fs = require('fs').promises
const { buildImage } = require('../helpers')
const { streamEvents, docker } = require('../../libs/docker')

//    'HEALTHCHECK --timeout=10s --start-period=10s --interval=5s CMD curl -I -s -f http://localhost/ || exit 1',
const publishStaticDocker = (configuration) => {
  return [
    'FROM nginx:stable-alpine',
    'COPY nginx.conf /etc/nginx/nginx.conf',
    'WORKDIR /usr/share/nginx/html',
    configuration.build.command.build
      ? `COPY --from=${configuration.build.container.name}:${configuration.build.container.tag} /usr/src/app/${configuration.publish.directory} ./`
      : `COPY ./${configuration.build.directory} ./`,
    'EXPOSE 80',
    'CMD ["nginx", "-g", "daemon off;"]'
  ].join('\n')
}

module.exports = async function (configuration) {
  const initialTag = configuration.build.container.tag
  if (configuration.build.command.build) {
    await buildImage(configuration)
    configuration.build.container.tag = 'cache'
  }
  await fs.writeFile(`${configuration.general.workdir}/Dockerfile`, publishStaticDocker(configuration))

  const stream = await docker.engine.buildImage(
    { src: ['.'], context: configuration.general.workdir },
    { t: `${configuration.build.container.name}:${initialTag}` }
  )
  await streamEvents(stream, configuration)
}

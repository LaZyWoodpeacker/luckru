<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated>
      <q-toolbar>
        <q-toolbar-title>Телефонный справочник </q-toolbar-title>

        <div>Quasar v{{ $q.version }}</div>
      </q-toolbar>
    </q-header>
    <q-page-container>
      <q-page padding>
        <q-table
          title="Контакты"
          :rows="mainList"
          row-key="id"
          :pagination="initialPagination"
        >
          <template v-slot:header="props">
            <q-tr :props="props">
              <q-th v-for="col in props.cols" :key="col['name']" :props="props">
                {{ col["label"] }}
              </q-th>
              <q-th auto-width />
            </q-tr>
          </template>

          <template v-slot:body="props">
            <q-tr :props="props">
              <q-td v-for="col in props.cols" :key="col['name']" :props="props">
                {{ col["value"] }}
              </q-td>
              <q-td>
                <q-btn
                  color="primary"
                  size="sm"
                  label="Изменить"
                  v-on:click="
                    OnChangeDlg(
                      props.cols[0].value,
                      props.cols[1].value,
                      props.cols[2].value,
                      props.cols[3].value
                    )
                  "
                />
                <q-btn
                  color="red"
                  size="sm"
                  label="Удалить"
                  v-on:click="OnDelete(props.cols[0].value)"
                />
              </q-td>
            </q-tr>
          </template>
        </q-table>

        <q-page-sticky position="bottom-right" :offset="[20, 20]">
          <q-btn fab icon="add" color="accent" v-on:click="OnAddDlg" />
        </q-page-sticky>
      </q-page>
      <q-inner-loading
        :showing="bisy"
        label="Ожидайте..."
        label-class="text-teal"
        label-style="font-size: 1.1em"
      />
    </q-page-container>
  </q-layout>

  <q-dialog v-model="DlgOpen">
    <q-card>
      <q-toolbar>
        <q-toolbar-title
          ><span class="text-weight-bold">{{
            id === -1 ? "Добавить" : "Изменить"
          }}</span>
        </q-toolbar-title>

        <q-btn flat round dense icon="close" v-close-popup />
      </q-toolbar>

      <q-card-section>
        <q-input
          outlined
          v-model="name"
          label="ФИО"
          :rules="[(val) => (val && val.length > 0) || 'Please type something']"
        />
        <q-input
          outlined
          v-model="phone"
          label="Телефон"
          :rules="[(val) => (val && val.length > 0) || 'Please type something']"
        />
        <q-input
          outlined
          v-model="place"
          label="Кем приходится"
          :rules="[(val) => (val && val.length > 0) || 'Please type something']"
        />
        <q-btn
          v-show="id === -1"
          label="Добавить"
          color="primary"
          v-on:click="OnAdd"
        />
        <q-btn
          v-show="id !== -1"
          label="Изменить"
          color="primary"
          v-on:click="OnChange"
        />
        <q-btn
          label="Отмена"
          v-close-popup
          color="primary"
          flat
          class="q-ml-sm"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
import { defineComponent, ref } from "vue";
import { api } from "boot/axios";

export default defineComponent({
  name: "PhoneDir",
  methods: {
    OnAdd() {
      if (this.name && this.phone && this.place) {
        this.DlgOpen = false;
        this.bisy = true;
        api
          .post("/api.php", {
            name: this.name,
            phone: this.phone,
            place: this.place,
          })
          .then((e) => {
            this.mainList.push({
              id: e.data,
              name: this.name,
              phone: this.phone,
              place: this.place,
            });
          })
          .catch((e) => {
            console.log(e);
          })
          .finally((e) => {
            this.bisy = false;
          });
      }
    },
    OnAddDlg() {
      this.id = -1;
      this.name = null;
      this.phone = null;
      this.place = null;
      this.DlgOpen = true;
    },
    OnChangeDlg(id, name, phone, place) {
      this.id = id;
      this.name = name;
      this.phone = phone;
      this.place = place;
      this.DlgOpen = true;
    },
    OnChange() {
      if (this.name && this.phone && this.place) {
        this.DlgOpen = false;
        this.bisy = true;
        api
          .patch("/api.php", {
            id: this.id,
            name: this.name,
            phone: this.phone,
            place: this.place,
          })
          .then((e) => {
            console.log("path");
            this.mainList[this.mainList.findIndex((e) => e.id === this.id)] = {
              id: this.id,
              name: this.name,
              phone: this.phone,
              place: this.place,
            };
          })
          .catch((e) => {
            console.log(e);
          })
          .finally((e) => {
            this.bisy = false;
          });
      }
    },
    OnDelete(id) {
      this.DlgOpen = false;
      this.bisy = true;
      api
        .delete("/api.php", {
          data: {
            id,
          },
        })
        .then((e) => {
          this.mainList.splice(
            this.mainList.findIndex((e) => e.id === id),
            1
          );
        })
        .catch((e) => {
          console.log(e);
        })
        .finally((e) => {
          this.bisy = false;
        });
    },
  },
  setup() {
    const DlgOpen = ref(false);
    const bisy = ref(true);
    const id = ref(-1);
    const name = ref(null);
    const phone = ref(null);
    const place = ref(null);

    const mainList = ref([]);
    api
      .get("/api.php")
      .then((e) => {
        mainList.value = e.data;
      })
      .catch((e) => {})
      .finally((e) => {
        bisy.value = false;
      });

    return {
      initialPagination: {
        sortBy: "desc",
        descending: false,
        rowsPerPage: 30,
        // rowsNumber: xx if getting data from a server
      },
      DlgOpen,
      name,
      phone,
      place,
      mainList,
      id,
      bisy,
    };
  },
});
</script>

<template>
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Quản lý điểm</h1>
      <ol class="breadcrumb">
        <li>
          <a href="/"><i class="fa fa-dashboard"></i> Trang chủ</a>
        </li>
        <li class="active">Quản lý điểm</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-12 col-sm-12">
          <div class="box">
            <div class="box-body">
              <form method="get">
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Mã hoặc tên lớp</label>
                      <input
                        type="text"
                        name="search"
                        v-model="filter.search"
                        class="form-control"
                      />
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <label>Niên khóa</label>
                      <select
                        name="nienkhoa"
                        class="form-control"
                        v-model="filter.nienkhoa"
                      >
                        <option value="-1">Tất cả</option>
                        <option
                          v-for="nk in listNienKhoa.select2"
                          :key="nk.id"
                          v-bind:value="nk.id"
                        >
                          {{ nk.text }}
                        </option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <label>Hệ đào tạo</label>
                      <select
                        name="chuongtrinh"
                        class="form-control"
                        v-model="filter.chuongtrinh"
                      >
                        <option
                          v-for="nk in listHeDaoTao.select2"
                          :key="nk.id"
                          v-bind:value="nk.id"
                        >
                          {{ nk.text }}
                        </option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-2" style="margin-top: 23px;">
                    <button type="submit" class="btn btn-default">
                      <i class="fa fa-search"></i> Tìm kiếm
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="col-md-12 col-sm-12">
          <div class="box box-widget">
            <div class="box-body table-responsive">
              <table class="table table-striped table-bordered no-margin">
                <thead>
                  <tr>
                    <th class="w-3 text-center">#</th>
                    <th class="w-10">Mã lớp</th>
                    <th>Lớp học</th>
                    <th>Khóa</th>
                    <th>Niên khóa</th>
                    <th>Chương trình đào tạo</th>
                    <!-- <th class="w-5">Số lượng sinh viên</th> -->
                    <th
                      class="text-center w-100-p"
                      v-if="quyenNhapRenLuyen || quyenNhapDiem"
                    >
                      Môn học
                    </th>
                    <th class="text-center w-100-p">Kết quả học tập</th>
                    <th class="text-center w-100-p" v-if="quyenNhapDiem">Thêm đợt thi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(lh, index) in table.list.data" :key="lh.lh_id">
                    <td class="text-center">{{ index + 1 }}</td>
                    <td>{{ lh.lh_ma }}</td>
                    <td>{{ lh.lh_ten }}</td>
                    <td>{{ lh.khoa_dao_tao.kdt_khoa }}</td>
                    <td>{{ lh.nien_khoa.nk_ten }}</td>
                    <td>{{ lh.khoa_dao_tao.kdt_ten }}</td>
                    <!-- <td class="text-center">{{ lh.sinh_vien_count }}</td> -->
                    <td class="text-center" v-if="quyenNhapRenLuyen || quyenNhapDiem">
                      <a
                        :href="lh.show_url"
                        title="Xem danh sách học kỳ và môn học"
                        class="btn bg-purple btn-sm"
                      >
                        <i class="fa fa-list-ol"></i> Nhập điểm
                      </a>
                    </td>
                    <td class="text-center">
                      <a
                        :href="lh.result_url"
                        title="Xem kết quả học tập của lớp"
                        class="btn bg-purple btn-sm"
                      >
                        <i class="fa fa-eye"></i> Kết quả học tập
                      </a>
                    </td>
                    <td class="text-center" v-if="quyenNhapDiem">
                      <button
                        v-on:click="preThemDotThi(lh.lh_id, lh.khoa_dao_tao.hdt_id)"
                        title="Thêm đợt dự xét thi tốt nghiệp"
                        class="btn btn-success btn-sm"
                      >
                        <i class="fa fa-plus"></i> Thêm đợt thi
                      </button>
                    </td>
                  </tr>
                  <tr v-if="table.list.data != null && table.list.data.length == 0">
                    <td colspan="99" class="text-center">Không tìm thấy dữ liệu</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="box-footer">
              <paginate :paginate="table.list" v-if="table.list != Object"></paginate>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- /.content -->
    <div class="modal fade" id="modal-chondotthi">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
            <h4 class="modal-title">Thêm đợt thi ({{ this.heDaoTao.hdt_id == 4 ? "Lớp cao đẳng" : "Lớp trung cấp" }})</h4>
          </div>
          <div class="modal-body">
            <select2 v-model="dotThi.dt_id" :options="listDotThi.select2"></select2>
          </div>
          <div class="modal-footer">
            <button type="button" v-on:click="themDotThi()" class="btn btn-danger">
              Lưu
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const consumer = {
  getListLopHoc: function () {
    const url = "/api/nhap-diem" + window.location.search;
    return axios.get(url).then((response) => response.data);
  },
  getLopHoc: function (lh_id) {
    const url = "/api/lop-hoc/" + lh_id;
    return axios.get(url).then((response) => response.data);
  },
  getListNienKhoa: function () {
    const url = "/api/nien-khoa/all";
    return axios.get(url).then((response) => response.data);
  },
  getListHeDaoTao: function () {
    const url = "/api/he-dao-tao/all?filter_role=1";
    return axios.get(url).then((response) => response.data);
  },
  getListDotThi: function (chuongtrinh) {
    const url = "/api/dot-thi/all?chuongtrinh=" + chuongtrinh;
    return axios.get(url).then((response) => response.data);
  },
  capNhatDotThiChoLop: function (formData) {
    return axios.post("/api/dot-thi/cap-nhat-dot-thi-cho-lop", formData);
  },
};

const store = {
  table: {
    set list(data) {
      window.localStorage.setItem("nhap-diem.table.list", JSON.stringify(data));
    },
    get list() {
      return window.localStorage.getItem("nhap-diem.table.list")
        ? JSON.parse(window.localStorage.getItem("nhap-diem.table.list"))
        : {};
    },
  },
};

export default {
  props: ["permissions"],
  mounted() {
    /* this.quyenTrungCap = this.permissions.includes("trungcap");
    this.quyenCaoDang = this.permissions.includes("caodang"); */
    this.quyenNhapDiem = this.permissions.includes("admin.score");
    this.quyenNhapRenLuyen = this.permissions.includes("nhaprenluyen");
    if (new URLSearchParams(window.location.search).get("chuongtrinh") != null) {
      this.filter.chuongtrinh = new URLSearchParams(window.location.search).get(
        "chuongtrinh"
      );
    } else if (
      new URLSearchParams(window.location.search).get("chuongtrinh") == null &&
      this.quyenCaoDang &&
      this.quyenTrungCap
    ) {
      this.filter.chuongtrinh = -1;
    } else if (this.quyenCaoDang) {
      this.filter.chuongtrinh = 4;
    } else {
      this.filter.chuongtrinh = 5;
    }
    this.reloadList();
    this.reloadListDm();
  },
  data() {
    return {
      quyenTrungCap: true,
      quyenCaoDang: true,
      quyenNhapDiem: false,
      quyenNhapRenLuyen: false,
      filter: {
        search: new URLSearchParams(window.location.search).get("search"),
        nienkhoa:
          new URLSearchParams(window.location.search).get("nienkhoa") == null
            ? -1
            : new URLSearchParams(window.location.search).get("nienkhoa"),
        chuongtrinh:
          new URLSearchParams(window.location.search).get("chuongtrinh") == null
            ? -1
            : new URLSearchParams(window.location.search).get("chuongtrinh"),
      },
      dotThi: {},
      heDaoTao: {
        hdt_id: null
      },
      table: {
        list: store.table.list,
      },
      listHeDaoTao: {
        select2: [],
      },
      listNienKhoa: {
        select2: [],
      },
      listDotThi: {
        select2: [],
      },
    };
  },
  methods: {
    preThemDotThi: function (lh_id, hdt_id) {
      this.dotThi.lh_id = lh_id;
    //   console.log(hdt_id);
      this.heDaoTao.hdt_id = hdt_id;
      this.reloadListDotThi();

      $("#modal-chondotthi").modal("show");
    },
    themDotThi: function () {
      consumer.capNhatDotThiChoLop(this.dotThi).then((data) => {
        $("#modal-chondotthi").modal("hide");
        AlertBox.Notify.Success("Cập nhật thành công");
      });
    },
    reloadList: function () {
      var vm = this;
      consumer.getListLopHoc().then((data) => {

        Vue.set(vm.table, "list", data);
        store.table.list = data;
      });
    },
    reloadListDm: function () {
      var vm = this;
      if (vm.listNienKhoa.select2.length == 0) {
        consumer.getListNienKhoa().then((data) => {
            console.log(data);
          vm.listNienKhoa.select2 = data.map((item) => {
            return {
              id: item.nk_id,
              text: item.nk_ten,
            };
          });
        });
      }

    //   if (vm.listDotThi.select2.length == 0) {
    //     let chuongtrinh = this.filter.chuongtrinh;
    //     console.log(chuongtrinh);
    //     consumer.getListDotThi(chuongtrinh).then((data) => {
    //       vm.listDotThi.select2 = data.map((item) => {
    //         return {
    //           id: item.dt_id,
    //           text: item.dt_ten + " - Thi tốt nghiệp",
    //         };
    //       });
    //     });
    //   }

      if (vm.listHeDaoTao.select2.length == 0) {
        consumer.getListHeDaoTao().then((data) => {
          vm.listHeDaoTao.select2 = data
            .filter((item) => {
              return (
                (item.hdt_ten.toLowerCase() == "cao đẳng" && this.quyenCaoDang) ||
                (item.hdt_ten.toLowerCase() == "trung cấp" && this.quyenTrungCap)
              );
            })
            .map((item) => {
              return {
                id: item.hdt_id,
                text: item.hdt_ten,
              };
            });

          if (this.quyenCaoDang && this.quyenTrungCap) {
            vm.listHeDaoTao.select2.unshift({
              id: -1,
              text: "Tất cả",
              selected: true,
            });
          }
        });
      }
    },
    reloadListDotThi: function () {
      var vm = this;

      if (vm.listDotThi.select2.length == 0) {
        let chuongtrinh = this.heDaoTao.hdt_id;
        console.log(chuongtrinh);
        consumer.getListDotThi(chuongtrinh).then((data) => {
          vm.listDotThi.select2 = data.map((item) => {
            return {
              id: item.dt_id,
              text: item.dt_ten + " - Thi tốt nghiệp",
            };
          });
        });
      }
    }
  },
};
</script>
